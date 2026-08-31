<?php

declare(strict_types=1);

namespace App\Services\Akar\Output;

use App\Exports\AnalisisExport;
use App\Models\Analisis;
use App\Models\Capaian;
use App\Models\RencanaAksi;
use App\Services\Akar\Analysis\ProfilCapaianService;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DokumenPdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * F8 — Ekspor laporan.
 *
 * Merakit hasil analisis (profil capaian, indikator prioritas beserta rincian
 * skor dan akar masalah, serta draf rencana tindak lanjut) menjadi dua bentuk:
 *
 *  - PDF: dokumen resmi yang dibawa ke rapat perencanaan. Latar putih, warna
 *    status tetap disertai ikon dan teks agar terbaca saat dicetak hitam putih
 *    (DESIGN.md bagian 8).
 *  - Excel: data mentah hasil analisis, satu baris per indikator prioritas,
 *    untuk diolah lebih lanjut.
 *
 * Komponen Livewire hanya memanggil kelas ini lalu mengembalikan responsnya;
 * tidak ada perakitan laporan di lapisan antarmuka.
 */
class LaporanExporter
{
    public function __construct(private readonly ProfilCapaianService $profilCapaian) {}

    /**
     * Bangun dokumen PDF laporan untuk sebuah analisis.
     */
    public function pdf(Analisis $analisis): DokumenPdf
    {
        return Pdf::loadView('laporan.pdf', $this->data($analisis))
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', false);
    }

    /**
     * Bangun objek ekspor Excel. Komponen pemanggil membungkusnya dengan
     * Excel::download() memakai nama berkas dari namaBerkas().
     */
    public function excel(Analisis $analisis): AnalisisExport
    {
        $analisis->loadMissing('wilayah', 'prioritas.indikator', 'prioritas.akar');

        return new AnalisisExport($analisis, $this->labelCapaian($analisis));
    }

    /**
     * Nama berkas unduhan, misalnya
     * "AKAR Kabupaten Bangkalan SMP Umum 2025.pdf".
     */
    public function namaBerkas(Analisis $analisis, string $ekstensi): string
    {
        $bagian = trim(sprintf(
            'AKAR %s %s %d',
            $analisis->wilayah?->namaTampilan() ?? 'Wilayah',
            $analisis->jenis_satuan,
            $analisis->tahun,
        ));

        // Rapikan agar aman sebagai nama berkas lintas sistem operasi.
        $bagian = preg_replace('/[\/\\\\:*?"<>|]+/', ' ', $bagian) ?? $bagian;
        $bagian = Str::squish((string) $bagian);

        return $bagian.'.'.$ekstensi;
    }

    /**
     * Susun seluruh data yang dibutuhkan blade laporan.
     *
     * @return array<string, mixed>
     */
    private function data(Analisis $analisis): array
    {
        $analisis->loadMissing('wilayah', 'prioritas.indikator', 'prioritas.akar');

        $profil = $this->profilCapaian->untukWilayah(
            $analisis->wilayah,
            $analisis->tahun,
            $analisis->jenis_satuan,
            $analisis->status_satuan,
        );

        $label = $this->labelCapaian($analisis);

        $prioritas = $analisis->prioritas
            ->sortBy('peringkat')
            ->map(function ($p) use ($label) {
                $akar = $p->akar
                    ->sortBy(fn ($a) => $this->urutanKeyakinan($a->keyakinan->value))
                    ->values();

                return [
                    'peringkat' => $p->peringkat,
                    'nomor' => $p->indikator?->nomor ?? '—',
                    'nama' => $p->indikator?->nama ?? 'Indikator tidak dikenal',
                    'skor' => (float) $p->skor,
                    'label' => $label[$p->indikator_id] ?? 'Tidak Tersedia',
                    'kalimat_penjelas' => $p->kalimat_penjelas,
                    'komponen_skor' => $this->normalkanKomponen($p->komponen_skor ?? []),
                    'akar' => $akar->map(fn ($a) => [
                        'label' => $a->label,
                        'keyakinan' => $a->keyakinan->label(),
                        'bukti' => $a->bukti ?? [],
                    ])->all(),
                ];
            })
            ->values()
            ->all();

        $rencana = RencanaAksi::with('item')->where('analisis_id', $analisis->id)->first();

        return [
            'analisis' => $analisis,
            'wilayah' => $analisis->wilayah?->namaTampilan() ?? 'Wilayah',
            'jenjang' => $analisis->jenis_satuan,
            'status' => $analisis->status_satuan,
            'tahun' => $analisis->tahun,
            'tanggal_cetak' => Carbon::now()->translatedFormat('d F Y'),
            'profil' => $profil,
            'prioritas' => $prioritas,
            'rencana' => $rencana,
        ];
    }

    /**
     * Label capaian tiap indikator prioritas, satu kueri.
     *
     * @return array<int, string> indikator_id => label
     */
    private function labelCapaian(Analisis $analisis): array
    {
        return Capaian::query()
            ->where('wilayah_id', $analisis->wilayah_id)
            ->where('tahun', $analisis->tahun)
            ->where('jenis_satuan', $analisis->jenis_satuan)
            ->where('status_satuan', $analisis->status_satuan)
            ->whereIn('indikator_id', $analisis->prioritas->pluck('indikator_id'))
            ->pluck('label_capaian', 'indikator_id')
            ->all();
    }

    /**
     * Samakan bentuk komponen skor. AnalisisRunner menyimpannya sebagai daftar
     * objek {kode, nama, bobot_maks, nilai_0_1, kontribusi}; factory pengujian
     * memakai bentuk asosiatif lama. Keluaran selalu daftar baris siap tampil.
     *
     * @param  array<mixed>  $komponen
     * @return list<array{nama: string, kontribusi: float, bobot_maks: float}>
     */
    private function normalkanKomponen(array $komponen): array
    {
        $hasil = [];

        foreach ($komponen as $kunci => $nilai) {
            if (! is_array($nilai)) {
                continue;
            }

            $hasil[] = [
                'nama' => (string) ($nilai['nama'] ?? ucfirst((string) $kunci)),
                'kontribusi' => (float) ($nilai['kontribusi'] ?? 0),
                'bobot_maks' => (float) ($nilai['bobot_maks'] ?? $nilai['bobot'] ?? 0),
            ];
        }

        return $hasil;
    }

    private function urutanKeyakinan(string $keyakinan): int
    {
        return match ($keyakinan) {
            'kuat' => 0,
            'sedang' => 1,
            'lemah' => 2,
            default => 3,
        };
    }
}

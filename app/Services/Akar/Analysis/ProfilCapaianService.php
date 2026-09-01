<?php

declare(strict_types=1);

namespace App\Services\Akar\Analysis;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Support\Collection;

/**
 * Menyusun profil capaian satu wilayah: indikator relevan untuk kombinasi
 * wilayah/tahun/jenjang/status, dikelompokkan menurut dimensi, lengkap dengan
 * label, arah perubahan, dan ambang resmi.
 *
 * "Indikator relevan" = indikator yang pernah muncul sebagai kolom di berkas
 * untuk jenjang itu, bukan seluruh 274 metadata. Kolom yang ada tapi tanpa baris
 * untuk wilayah terpilih diperlakukan "Tidak Tersedia" (baris seperti itu tidak
 * disimpan saat impor), ditampilkan terpisah, bukan nol.
 *
 * Mode satuan: sumber data berkas kepala sekolah (ImporBerkas jenis 'satuan'),
 * disaring kolom `tersedia_satuan`. Bentuk keluaran identik dengan jalur daerah.
 */
class ProfilCapaianService
{
    /**
     * @return array{
     *   wilayah: array{nama: string, level: string},
     *   tahun: int,
     *   jenis_satuan: string,
     *   status_satuan: string,
     *   tersedia: bool,
     *   ringkasan: array{merah: int, kuning: int, hijau: int, tidak_tersedia: int, total: int},
     *   dimensi: array<string, array{kode: string, nama: string, indikator: list<array<string, mixed>>, hitung: array<string, int>}>,
     *   dimensi_grafik: list<array{kode: string, nama: string, hitung: array<string, int>}>,
     *   tidak_tersedia: list<array<string, mixed>>
     * }
     */
    public function untukWilayah(Wilayah $wilayah, int $tahun, string $jenisSatuan, string $statusSatuan): array
    {
        $rangka = [
            'wilayah' => ['nama' => $wilayah->namaTampilan(), 'level' => $wilayah->level],
            'tahun' => $tahun,
            'jenis_satuan' => $jenisSatuan,
            'status_satuan' => $statusSatuan,
            'tersedia' => false,
            'ringkasan' => ['merah' => 0, 'kuning' => 0, 'hijau' => 0, 'tidak_tersedia' => 0, 'total' => 0],
            'dimensi' => [],
            'dimensi_grafik' => [],
            'tidak_tersedia' => [],
        ];

        $satuan = $wilayah->level === 'satuan';

        $impor = $satuan
            ? $this->imporSatuan($wilayah, $tahun)
            : $this->imporDaerah($tahun);
        if ($impor === null) {
            return $rangka;
        }

        $jenisLayanan = PemetaanJenisLayanan::dari($jenisSatuan);
        $kolomKetersediaan = $satuan ? 'tersedia_satuan' : 'tersedia_kabkota';
        $universe = $this->indikatorRelevan($impor->id, $jenisSatuan, $jenisLayanan, $kolomKetersediaan);
        if ($universe->isEmpty()) {
            return $rangka;
        }

        $capaian = Capaian::query()
            ->where('wilayah_id', $wilayah->id)
            ->where('tahun', $tahun)
            ->where('jenis_satuan', $jenisSatuan)
            ->where('status_satuan', $statusSatuan)
            ->get(['indikator_id', 'label_capaian', 'perubahan_nilai'])
            ->keyBy('indikator_id');

        $namaDimensi = (array) config('akar.dimensi', []);
        $labelMerah = (array) config('akar.label_merah', ['Kurang']);
        $labelKuning = (array) config('akar.label_kuning', ['Sedang']);
        $labelHijau = (array) config('akar.label_hijau', ['Baik']);

        $dimensi = [];
        $tidakTersedia = [];
        $ringkasan = $rangka['ringkasan'];

        foreach ($universe as $indikator) {
            $baris = $capaian->get($indikator->id);
            $label = $baris->label_capaian ?? 'Tidak Tersedia';

            $status = match (true) {
                in_array($label, $labelMerah, true) => 'merah',
                in_array($label, $labelKuning, true) => 'kuning',
                in_array($label, $labelHijau, true) => 'hijau',
                default => 'kosong',
            };

            $entri = [
                'nomor' => $indikator->nomor,
                'nama' => $indikator->nama,
                'induk_nomor' => $indikator->induk?->nomor,
                'label_capaian' => $label,
                'perubahan_nilai' => $baris->perubahan_nilai ?? 'Tidak Tersedia',
                'status' => $status,
                'ambang' => [
                    'merah' => $indikator->definisi_merah,
                    'kuning' => $indikator->definisi_kuning,
                    'hijau' => $indikator->definisi_hijau,
                ],
            ];

            $ringkasan['total']++;

            // Hitungan per dimensi mencakup "Tidak Tersedia" untuk grafik komposisi.
            $kode = $indikator->dimensi;
            $dimensi[$kode] ??= [
                'kode' => $kode,
                'nama' => $namaDimensi[$kode] ?? $kode,
                'indikator' => [],
                'hitung' => ['merah' => 0, 'kuning' => 0, 'hijau' => 0, 'kosong' => 0],
            ];
            $dimensi[$kode]['hitung'][$status]++;

            if ($status === 'kosong') {
                $ringkasan['tidak_tersedia']++;
                $tidakTersedia[] = $entri;

                continue;
            }

            $ringkasan[$status]++;
            $dimensi[$kode]['indikator'][] = $entri;
        }

        // Dimensi yang hanya berisi "Tidak Tersedia": dibuang dari tabel, tetap di grafik.
        $dimensiGrafik = array_values(array_filter(
            $dimensi,
            static fn ($d) => array_sum($d['hitung']) > 0,
        ));
        usort($dimensiGrafik, static fn ($a, $b) => strcmp($a['kode'], $b['kode']));

        $dimensi = array_filter($dimensi, static fn ($d) => $d['indikator'] !== []);
        ksort($dimensi);

        return [
            ...$rangka,
            'tersedia' => true,
            'ringkasan' => $ringkasan,
            'dimensi' => $dimensi,
            'dimensi_grafik' => $dimensiGrafik,
            'tidak_tersedia' => $tidakTersedia,
        ];
    }

    /** Impor berkas daerah terakhir yang berhasil untuk sebuah tahun edisi. */
    private function imporDaerah(int $tahun): ?ImporBerkas
    {
        return ImporBerkas::query()
            ->where('jenis', 'daerah')
            ->where('tahun_edisi', $tahun)
            ->where('status', 'selesai')
            ->latest('id')
            ->first();
    }

    /** Impor berkas satuan terakhir yang menghasilkan capaian untuk sekolah ini. */
    private function imporSatuan(Wilayah $wilayah, int $tahun): ?ImporBerkas
    {
        $imporIds = Capaian::query()
            ->where('wilayah_id', $wilayah->id)
            ->where('tahun', $tahun)
            ->distinct()
            ->pluck('impor_id');

        if ($imporIds->isEmpty()) {
            return null;
        }

        return ImporBerkas::query()
            ->whereIn('id', $imporIds)
            ->where('jenis', 'satuan')
            ->where('status', 'selesai')
            ->latest('id')
            ->first();
    }

    /**
     * Indikator yang jadi kolom di berkas untuk jenjang ini, terurut natural.
     *
     * @param  string  $kolomKetersediaan  'tersedia_kabkota' (daerah) / 'tersedia_satuan' (sekolah)
     * @return Collection<int, Indikator>
     */
    private function indikatorRelevan(int $imporId, string $jenisSatuan, string $jenisLayanan, string $kolomKetersediaan): Collection
    {
        $idRelevan = Capaian::query()
            ->where('impor_id', $imporId)
            ->where('jenis_satuan', $jenisSatuan)
            ->distinct()
            ->pluck('indikator_id');

        if ($idRelevan->isEmpty()) {
            return collect();
        }

        return Indikator::query()
            ->with('induk:id,nomor')
            ->whereIn('id', $idRelevan)
            ->where('jenis_layanan', $jenisLayanan)
            ->where($kolomKetersediaan, true)
            ->get()
            ->sortBy(fn (Indikator $i) => $this->kunciUrut($i->nomor), SORT_NATURAL)
            ->values();
    }

    /** "A.1.10" -> "A.001.010" supaya A.1.10 tak diurutkan sebelum A.1.2. */
    private function kunciUrut(string $nomor): string
    {
        return preg_replace_callback('/\d+/', static fn ($m) => str_pad($m[0], 3, '0', STR_PAD_LEFT), $nomor) ?? $nomor;
    }
}

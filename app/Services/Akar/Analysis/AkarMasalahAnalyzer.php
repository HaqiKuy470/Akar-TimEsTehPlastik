<?php

declare(strict_types=1);

namespace App\Services\Akar\Analysis;

use App\Enums\Keyakinan;
use App\Models\Analisis;
use App\Models\AnalisisAkar;
use App\Models\AnalisisPrioritas;
use App\Models\Capaian;
use App\Models\Indikator;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * F4 - Analisis Akar Masalah.
 *
 * Menelusuri dari gejala (indikator prioritas berlabel merah) ke penyebab.
 * Pohon keputusannya tidak tertanam di kode, melainkan dibaca dari
 * config/intervensi.php. Untuk setiap indikator prioritas:
 *
 *   1. Ambil daftar kandidat akar masalah dari konfigurasi. Bila indikator
 *      belum dipetakan, kembalikan koleksi kosong; pemanggil menyatakan
 *      "rekomendasi belum tersedia" dan TIDAK mengarang.
 *   2. Untuk tiap kandidat, periksa label capaian indikator pendukungnya pada
 *      kombinasi wilayah/tahun/jenjang/status yang sama dengan analisis induk.
 *   3. Terapkan gerbang 'ambang'. Bila ada kandidat yang lolos gerbang, hanya
 *      kandidat itulah yang menjadi hipotesis akar masalah. Bila tidak ada
 *      satu pun yang lolos, seluruh kandidat tetap dicatat dengan keyakinan
 *      "tidak_cukup_bukti" - sistem menyatakan keterbatasannya secara jujur
 *      alih-alih memaksakan kesimpulan (PRD F4).
 *   4. Tingkat keyakinan mengikuti tabel di ARCHITECTURE.md bagian 6.3,
 *      dihitung dari jumlah indikator pendukung berlabel Kurang atau Sedang.
 *
 * Indikator pendukung berlabel "Tidak Tersedia" (termasuk yang tidak punya
 * baris capaian sama sekali) tidak dihitung sebagai bukti maupun sebagai
 * populasi gerbang 'mayoritas_bermasalah'.
 */
class AkarMasalahAnalyzer
{
    /** Label yang dihitung sebagai bukti pendukung sebuah akar masalah. */
    private const LABEL_BUKTI = ['Kurang', 'Sedang'];

    private const TIDAK_TERSEDIA = 'Tidak Tersedia';

    /**
     * Telusuri akar masalah untuk satu indikator prioritas dan simpan hasilnya.
     *
     * Idempoten: baris analisis_akar lama milik prioritas ini dihapus lebih
     * dulu, sehingga menjalankan ulang menghasilkan keadaan yang sama.
     *
     * @return Collection<int, AnalisisAkar> diurutkan dari keyakinan terkuat
     */
    public function telusuri(AnalisisPrioritas $prioritas): Collection
    {
        $prioritas->loadMissing('indikator', 'analisis');

        $nomorIndikator = $prioritas->indikator?->nomor;
        $analisis = $prioritas->analisis;

        $peta = $this->petaIntervensi();

        if ($nomorIndikator === null || $analisis === null || ! isset($peta[$nomorIndikator])) {
            return collect();
        }

        $kandidatList = $peta[$nomorIndikator]['kandidat_akar'] ?? [];

        // Nilai tiap kandidat lebih dulu, tanpa menyentuh basis data.
        $dinilai = array_map(function (array $kandidat) use ($analisis): array {
            $pendukung = $this->labelPendukung($analisis, $kandidat['periksa'] ?? []);

            return [
                'kandidat' => $kandidat,
                'pendukung' => $pendukung,
                'lolos' => $this->lolosAmbang((string) ($kandidat['ambang'] ?? ''), $pendukung),
                'keyakinan' => $this->hitungKeyakinan($pendukung),
            ];
        }, $kandidatList);

        $adaYangLolos = collect($dinilai)->contains(fn (array $d) => $d['lolos']);

        $prioritas->akar()->delete();

        $baris = collect();
        foreach ($dinilai as $d) {
            // Bila ada kandidat yang lolos gerbang, kandidat yang tidak lolos
            // diabaikan: hipotesis yang lebih didukung data yang menang.
            if ($adaYangLolos && ! $d['lolos']) {
                continue;
            }

            $keyakinan = $adaYangLolos ? $d['keyakinan'] : Keyakinan::TidakCukupBukti;

            $baris->push($prioritas->akar()->create([
                'kode_akar' => $d['kandidat']['kode'],
                'label' => $d['kandidat']['label'],
                'bukti' => $this->kumpulkanBukti($d['pendukung']),
                'keyakinan' => $keyakinan,
            ]));
        }

        return $baris
            ->sortBy(fn (AnalisisAkar $a) => $this->urutanKeyakinan($a->keyakinan))
            ->values();
    }

    /**
     * Telusuri akar masalah untuk seluruh indikator prioritas dalam sebuah
     * analisis.
     *
     * @return Collection<int, AnalisisAkar>
     */
    public function telusuriAnalisis(Analisis $analisis): Collection
    {
        return $analisis->prioritas()
            ->with('indikator')
            ->get()
            ->flatMap(fn (AnalisisPrioritas $prioritas) => $this->telusuri($prioritas));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function petaIntervensi(): array
    {
        return (array) config('intervensi', []);
    }

    /**
     * Label capaian tiap indikator pendukung untuk kombinasi analisis ini.
     * Indikator yang tidak ada di tabel indikator, atau ada tetapi tidak punya
     * baris capaian, dilaporkan sebagai "Tidak Tersedia".
     *
     * @param  list<string>  $nomorPeriksa
     * @return list<array{nomor: string, nama: string|null, label: string}>
     */
    private function labelPendukung(Analisis $analisis, array $nomorPeriksa): array
    {
        if ($nomorPeriksa === []) {
            return [];
        }

        $jenisLayanan = PemetaanJenisLayanan::dari((string) $analisis->jenis_satuan);

        $indikator = Indikator::query()
            ->whereIn('nomor', $nomorPeriksa)
            ->where('jenis_layanan', $jenisLayanan)
            ->get(['id', 'nomor', 'nama'])
            ->keyBy('nomor');

        $labelPerIndikator = $indikator->isEmpty()
            ? collect()
            : Capaian::query()
                ->where('wilayah_id', $analisis->wilayah_id)
                ->where('tahun', $analisis->tahun)
                ->where('jenis_satuan', $analisis->jenis_satuan)
                ->where('status_satuan', $analisis->status_satuan)
                ->whereIn('indikator_id', $indikator->pluck('id'))
                ->pluck('label_capaian', 'indikator_id');

        $hasil = [];
        foreach ($nomorPeriksa as $nomor) {
            $satu = $indikator->get($nomor);
            $label = $satu !== null
                ? ($labelPerIndikator->get($satu->id) ?? self::TIDAK_TERSEDIA)
                : self::TIDAK_TERSEDIA;

            $hasil[] = [
                'nomor' => $nomor,
                'nama' => $satu?->nama,
                'label' => $label,
            ];
        }

        return $hasil;
    }

    /**
     * @param  list<array{nomor: string, nama: string|null, label: string}>  $pendukung
     */
    private function lolosAmbang(string $ambang, array $pendukung): bool
    {
        $kurang = $this->hitungLabel($pendukung, 'Kurang');
        $sedang = $this->hitungLabel($pendukung, 'Sedang');
        $populasi = count(array_filter($pendukung, fn (array $p) => $p['label'] !== self::TIDAK_TERSEDIA));

        return match ($ambang) {
            'minimal_satu_kurang' => $kurang >= 1,
            'minimal_dua_kurang' => $kurang >= 2,
            'mayoritas_bermasalah' => $populasi > 0 && ($kurang + $sedang) / $populasi > 0.5,
            default => throw new InvalidArgumentException(
                "Ambang '{$ambang}' pada config/intervensi.php tidak dikenali. ".
                'Gunakan: minimal_satu_kurang, minimal_dua_kurang, atau mayoritas_bermasalah.'
            ),
        };
    }

    /**
     * Tabel keyakinan ARCHITECTURE.md bagian 6.3.
     *
     * @param  list<array{nomor: string, nama: string|null, label: string}>  $pendukung
     */
    private function hitungKeyakinan(array $pendukung): Keyakinan
    {
        $kurang = $this->hitungLabel($pendukung, 'Kurang');
        $sedang = $this->hitungLabel($pendukung, 'Sedang');

        return match (true) {
            $kurang >= 2 => Keyakinan::Kuat,
            $kurang === 1 => Keyakinan::Sedang,
            $sedang >= 2 => Keyakinan::Sedang,
            $sedang === 1 => Keyakinan::Lemah,
            default => Keyakinan::TidakCukupBukti,
        };
    }

    /**
     * @param  list<array{nomor: string, nama: string|null, label: string}>  $pendukung
     */
    private function hitungLabel(array $pendukung, string $label): int
    {
        return count(array_filter($pendukung, fn (array $p) => $p['label'] === $label));
    }

    /**
     * Hanya indikator pendukung berlabel Kurang atau Sedang yang dicatat
     * sebagai bukti; indikator Baik atau Tidak Tersedia tidak memperkuat
     * hipotesis akar masalah.
     *
     * @param  list<array{nomor: string, nama: string|null, label: string}>  $pendukung
     * @return list<array{nomor: string, nama: string|null, label: string}>
     */
    private function kumpulkanBukti(array $pendukung): array
    {
        return array_values(array_filter(
            $pendukung,
            fn (array $p) => in_array($p['label'], self::LABEL_BUKTI, true),
        ));
    }

    private function urutanKeyakinan(Keyakinan $keyakinan): int
    {
        return match ($keyakinan) {
            Keyakinan::Kuat => 0,
            Keyakinan::Sedang => 1,
            Keyakinan::Lemah => 2,
            Keyakinan::TidakCukupBukti => 3,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Akar\Parsers;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\PemetaanJenisLayanan;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

/**
 * Sheet provinsi berkas Rapor Pendidikan Indonesia -> tabel `wilayah` + `capaian`.
 *
 * Memuat satu sheet sekaligus via PhpSpreadsheet lalu melepasnya; WithChunkReading
 * membuka ulang berkas 21 MB per potongan (terlalu lambat untuk 38 sheet). Impor
 * daerah hanya jalan di lokal, jadi memory_limit dinaikkan di perintah artisan.
 *
 * Baris berlabel "Tidak Tersedia" tidak disimpan (mayoritas kombinasi wilayah x
 * indikator kosong -> jutaan baris, menabrak kuota cPanel). Ketiadaan baris = kosong.
 */
class CapaianDaerahParser
{
    private const LABEL_SAH = ['Baik', 'Sedang', 'Kurang', 'Tidak Tersedia'];

    private const PERUBAHAN_SAH = ['Naik', 'Turun', 'Tidak berubah', 'Tidak Tersedia'];

    private const NILAI_KOSONG = 'Tidak Tersedia';

    private const UKURAN_BATCH = 1000;

    /**
     * @var array{
     *   nomorNama: array<string, int>,
     *   nomorJenis: array<string, int>,
     *   nomor: array<string, list<int>>
     * }|null
     */
    private ?array $indeksIndikator = null;

    /** Kunci "level|provinsi|kabkota" => id. */
    private array $cacheWilayah = [];

    private array $indikatorTakDikenal = [];

    public function __construct(private readonly HeaderResolver $headerResolver) {}

    /**
     * Impor seluruh sheet provinsi dari satu berkas .xlsx. Idempoten lewat hash
     * berkas; percobaan ulang menghapus capaian lama lebih dulu.
     *
     * @param  callable(string $namaSheet, int $indeks, int $total): void|null  $laporProgres
     */
    public function impor(string $path, ?callable $laporProgres = null): ImporBerkas
    {
        if (! is_file($path)) {
            throw new RuntimeException("Berkas tidak ditemukan: {$path}");
        }

        $hash = hash_file('sha256', $path);
        $impor = ImporBerkas::firstOrNew(['hash_berkas' => $hash]);

        if ($impor->exists && $impor->status === 'selesai') {
            return $impor;
        }

        $sheetProvinsi = $this->daftarSheetProvinsi($path);
        if ($sheetProvinsi === []) {
            throw new RuntimeException(
                'Berkas tidak memuat satu pun sheet provinsi. '.
                'Pastikan berkas adalah Data Rapor Pendidikan Indonesia yang diunduh dari data.kemendikdasmen.go.id.'
            );
        }

        $tahun = $this->deteksiTahun($path, $sheetProvinsi[0]);

        $impor->fill([
            'nama_berkas' => basename($path),
            'jenis' => 'daerah',
            'tahun_edisi' => $tahun,
            'status' => 'proses',
            'catatan_galat' => null,
        ])->save();

        // Ulang dari bersih bila ini percobaan kedua.
        Capaian::where('impor_id', $impor->id)->delete();

        $this->indeksIndikator = null;
        $this->cacheWilayah = [];
        $this->indikatorTakDikenal = [];

        $totalBaris = 0;
        try {
            foreach ($sheetProvinsi as $i => $namaSheet) {
                if ($laporProgres !== null) {
                    $laporProgres($namaSheet, $i + 1, count($sheetProvinsi));
                }
                $totalBaris += $this->imporSheet($path, $namaSheet, $impor, $tahun);
            }
        } catch (\Throwable $e) {
            $impor->fill(['status' => 'gagal', 'catatan_galat' => $e->getMessage()])->save();
            throw $e;
        }

        $catatan = $this->indikatorTakDikenal !== []
            ? 'Indikator di header yang tidak ada di tabel indikator: '.implode(', ', array_keys($this->indikatorTakDikenal))
            : null;

        $impor->fill([
            'status' => 'selesai',
            'jumlah_baris' => $totalBaris,
            'diproses_pada' => now(),
            'catatan_galat' => $catatan,
        ])->save();

        return $impor;
    }

    /**
     * Daftar nama sheet provinsi (tanpa Metadata dan Nasional). Dipakai jalur
     * antrean yang memecah impor menjadi satu job per sheet.
     *
     * @return list<string>
     */
    public function sheetProvinsi(string $path): array
    {
        return $this->daftarSheetProvinsi($path);
    }

    /**
     * Deteksi tahun edisi dari isi berkas. Dipakai jalur antrean untuk mengunci
     * tahun sebelum sheet dipecah menjadi job terpisah.
     */
    public function deteksiTahunBerkas(string $path): int
    {
        $sheet = $this->daftarSheetProvinsi($path);
        if ($sheet === []) {
            throw new RuntimeException(
                'Berkas tidak memuat satu pun sheet provinsi. '.
                'Pastikan berkas adalah Data Rapor Pendidikan Indonesia yang diunduh dari data.kemendikdasmen.go.id.'
            );
        }

        return $this->deteksiTahun($path, $sheet[0]);
    }

    /** Hapus capaian satu provinsi untuk sebuah impor, agar job sheet aman diulang. */
    public function bersihkanSheet(int $imporId, string $provinsi): void
    {
        Capaian::query()
            ->where('impor_id', $imporId)
            ->whereIn('wilayah_id', Wilayah::query()->where('provinsi', $provinsi)->select('id'))
            ->delete();
    }

    /** Impor satu sheet provinsi; mengembalikan jumlah baris data yang diproses. */
    public function imporSheet(string $path, string $namaSheet, ImporBerkas $impor, ?int $tahun = null): int
    {
        $tahun ??= $this->deteksiTahun($path, $namaSheet);

        $sheet = $this->muatSheet($path, $namaSheet);

        $kolomTertinggi = $sheet->getHighestColumn();
        $headerMentah = $sheet->rangeToArray("A6:{$kolomTertinggi}8", null, false, false, false);

        $peta = $this->headerResolver->resolve($headerMentah[0], $headerMentah[1], $headerMentah[2]);
        $dimensi = $this->kolomDimensi($peta);
        $pasangan = $this->pasanganIndikator($peta);
        $kolomTerakhir = $this->kolomTerakhir($peta);
        $hurufTerakhir = Coordinate::stringFromColumnIndex($kolomTerakhir);

        $this->indeksIndikator ??= $this->bangunIndeksIndikator();

        $jumlah = 0;
        $batch = [];

        foreach ($sheet->getRowIterator(9) as $baris) {
            $nilai = $this->bacaBaris($baris, $hurufTerakhir, $kolomTerakhir);
            if ($this->barisKosong($nilai)) {
                continue;
            }

            $provinsi = $this->sel($nilai, $dimensi['provinsi'] ?? null);
            if ($provinsi === null) {
                continue;
            }
            $kabkota = $this->sel($nilai, $dimensi['kabupaten_kota'] ?? null);
            $jenisSatuan = $this->sel($nilai, $dimensi['jenis_satuan'] ?? null) ?? '';
            $statusSatuan = $this->sel($nilai, $dimensi['status_satuan'] ?? null) ?? '';

            $wilayahId = $this->wilayahId($provinsi, $kabkota);
            $jenisLayanan = $this->jenisLayananDari($jenisSatuan);

            $jumlah++;

            foreach ($pasangan as $p) {
                $label = $this->bakukanLabel($this->sel($nilai, $p['kolom_label']));
                if ($label === self::NILAI_KOSONG) {
                    continue; // tidak disimpan, lihat catatan kelas
                }

                $indikatorId = $this->indikatorId($p['nomor'], $p['nama'], $jenisLayanan);
                if ($indikatorId === null) {
                    $this->indikatorTakDikenal[$p['nomor'].' '.$p['nama']] = true;

                    continue;
                }

                $perubahan = $this->bakukanPerubahan(
                    $p['kolom_perubahan'] !== null ? $this->sel($nilai, $p['kolom_perubahan']) : null
                );

                $batch[] = [
                    'impor_id' => $impor->id,
                    'wilayah_id' => $wilayahId,
                    'indikator_id' => $indikatorId,
                    'tahun' => $tahun,
                    'jenis_satuan' => mb_substr($jenisSatuan, 0, 64),
                    'status_satuan' => mb_substr($statusSatuan, 0, 32),
                    'label_capaian' => $label,
                    'perubahan_nilai' => $perubahan,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= self::UKURAN_BATCH) {
                    Capaian::insert($batch);
                    $batch = [];
                }
            }
        }

        if ($batch !== []) {
            Capaian::insert($batch);
        }

        // Lepas sheet dari memori sebelum sheet berikutnya.
        $sheet->getParent()?->disconnectWorksheets();
        gc_collect_cycles();

        return $jumlah;
    }

    // ------------------------------------------------------------------
    // Pembacaan berkas
    // ------------------------------------------------------------------

    private function muatSheet(string $path, string $namaSheet): Worksheet
    {
        $reader = new Xlsx;
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $reader->setLoadSheetsOnly([$namaSheet]);

        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getSheetByName($namaSheet);

        if ($sheet === null) {
            throw new RuntimeException("Sheet '{$namaSheet}' tidak ditemukan di berkas.");
        }

        return $sheet;
    }

    /**
     * @return list<string>
     */
    private function daftarSheetProvinsi(string $path): array
    {
        $reader = new Xlsx;
        $bukanProvinsi = ['Metadata', 'Nasional'];

        return array_values(array_filter(
            $reader->listWorksheetNames($path),
            static fn (string $nama) => ! in_array($nama, $bukanProvinsi, true),
        ));
    }

    /**
     * Tahun edisi dari isi berkas, bukan nama berkas (nama berkas kerap memuat
     * dua tahun). Judul kolom baris 8 "Label Capaian 2025" -> 2025.
     */
    private function deteksiTahun(string $path, string $namaSheet): int
    {
        $reader = new Xlsx;
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([$namaSheet]);
        $reader->setReadFilter(new BatasBaris(1, 8));

        $sheet = $reader->load($path)->getSheetByName($namaSheet);
        if ($sheet === null) {
            throw new RuntimeException("Sheet '{$namaSheet}' tidak ditemukan saat mendeteksi tahun.");
        }

        $kolomTertinggi = $sheet->getHighestColumn();
        $baris = $sheet->rangeToArray("A1:{$kolomTertinggi}8", null, false, false, false);

        foreach ([$baris[7] ?? [], $baris[0] ?? []] as $kandidat) {
            foreach ($kandidat as $sel) {
                if (is_string($sel) && preg_match('/(20\d{2})/', $sel, $m)) {
                    return (int) $m[1];
                }
            }
        }

        throw new RuntimeException(
            "Tahun edisi tidak dapat dideteksi dari sheet '{$namaSheet}'. ".
            'Struktur berkas tidak sesuai format Rapor Pendidikan Indonesia.'
        );
    }

    /**
     * @return array<int, string|int|float|null> nilai per nomor kolom (1-based)
     */
    private function bacaBaris(Row $baris, string $hurufTerakhir, int $kolomTerakhir): array
    {
        $nilai = [];
        $iterator = $baris->getCellIterator('A', $hurufTerakhir);
        $iterator->setIterateOnlyExistingCells(false);

        foreach ($iterator as $sel) {
            $kolom = Coordinate::columnIndexFromString($sel->getColumn());
            if ($kolom > $kolomTerakhir) {
                break;
            }
            $isi = $sel->getValue();
            $nilai[$kolom] = is_string($isi) ? trim($isi) : $isi;
        }

        return $nilai;
    }

    // ------------------------------------------------------------------
    // Peta header
    // ------------------------------------------------------------------

    /**
     * @param  array<int, array<string, string|null>>  $peta
     * @return array<string, int> nama dimensi => nomor kolom
     */
    private function kolomDimensi(array $peta): array
    {
        $dimensi = [];
        foreach ($peta as $kolom => $entri) {
            if (($entri['jenis'] ?? null) === 'dimensi') {
                $dimensi[$entri['dimensi']] = $kolom;
            }
        }

        return $dimensi;
    }

    /**
     * Pasangkan kolom label dengan kolom perubahan untuk tiap indikator.
     *
     * @param  array<int, array<string, string|null>>  $peta
     * @return list<array{nomor:string, nama:string, kolom_label:int, kolom_perubahan:int|null}>
     */
    private function pasanganIndikator(array $peta): array
    {
        ksort($peta);
        $pasangan = [];
        $labelTertunda = null;

        foreach ($peta as $kolom => $entri) {
            if (($entri['jenis'] ?? null) !== 'indikator') {
                continue;
            }

            if ($entri['peran'] === 'label') {
                if ($labelTertunda !== null) {
                    $pasangan[] = $labelTertunda + ['kolom_perubahan' => null];
                }
                $labelTertunda = [
                    'nomor' => $entri['nomor'],
                    'nama' => $entri['nama'],
                    'kolom_label' => $kolom,
                ];

                continue;
            }

            // peran perubahan
            if ($labelTertunda !== null && $labelTertunda['nomor'] === $entri['nomor']) {
                $pasangan[] = $labelTertunda + ['kolom_perubahan' => $kolom];
                $labelTertunda = null;
            }
        }

        if ($labelTertunda !== null) {
            $pasangan[] = $labelTertunda + ['kolom_perubahan' => null];
        }

        return $pasangan;
    }

    /**
     * @param  array<int, array<string, string|null>>  $peta
     */
    private function kolomTerakhir(array $peta): int
    {
        return $peta === [] ? 4 : max(array_keys($peta));
    }

    // ------------------------------------------------------------------
    // Pemetaan indikator
    // ------------------------------------------------------------------

    /**
     * @return array{nomorNama: array<string,int>, nomorJenis: array<string,int>, nomor: array<string,list<int>>}
     */
    private function bangunIndeksIndikator(): array
    {
        $nomorNama = [];
        $nomorJenis = [];
        $nomor = [];

        Indikator::query()
            ->select(['id', 'nomor', 'nama', 'jenis_layanan'])
            ->orderBy('id')
            ->chunk(500, function ($kelompok) use (&$nomorNama, &$nomorJenis, &$nomor) {
                foreach ($kelompok as $indikator) {
                    $nomorNama[$indikator->nomor.'|'.$indikator->nama] ??= $indikator->id;
                    $nomorJenis[$indikator->nomor.'|'.$indikator->jenis_layanan] ??= $indikator->id;
                    $nomor[$indikator->nomor][] = $indikator->id;
                }
            });

        return ['nomorNama' => $nomorNama, 'nomorJenis' => $nomorJenis, 'nomor' => $nomor];
    }

    /**
     * Prioritas pencocokan: (nomor + nama) -> (nomor + jenis layanan) ->
     * satu-satunya kandidat dengan nomor itu -> null.
     */
    private function indikatorId(string $nomor, string $nama, string $jenisLayanan): ?int
    {
        $indeks = $this->indeksIndikator;

        if (isset($indeks['nomorNama'][$nomor.'|'.$nama])) {
            return $indeks['nomorNama'][$nomor.'|'.$nama];
        }

        if (isset($indeks['nomorJenis'][$nomor.'|'.$jenisLayanan])) {
            return $indeks['nomorJenis'][$nomor.'|'.$jenisLayanan];
        }

        $kandidat = $indeks['nomor'][$nomor] ?? [];

        return count($kandidat) === 1 ? $kandidat[0] : null;
    }

    private function jenisLayananDari(string $jenisSatuan): string
    {
        return PemetaanJenisLayanan::dari($jenisSatuan);
    }

    // ------------------------------------------------------------------
    // Wilayah
    // ------------------------------------------------------------------

    /**
     * Baris dengan Kabupaten/Kota bernilai "-" adalah agregat provinsi, disimpan
     * sebagai wilayah level provinsi dan dipakai sebagai pembanding.
     */
    private function wilayahId(string $provinsi, ?string $kabkota): int
    {
        $agregatProvinsi = $kabkota === null || $kabkota === '' || $kabkota === '-';

        if ($agregatProvinsi) {
            return $this->wilayahProvinsiId($provinsi);
        }

        $kunci = 'kabkota|'.$provinsi.'|'.$kabkota;
        if (isset($this->cacheWilayah[$kunci])) {
            return $this->cacheWilayah[$kunci];
        }

        $wilayah = Wilayah::firstOrCreate(
            ['level' => 'kabkota', 'provinsi' => $provinsi, 'kabupaten_kota' => $kabkota, 'nama_satuan' => null],
            ['induk_id' => $this->wilayahProvinsiId($provinsi)],
        );

        return $this->cacheWilayah[$kunci] = $wilayah->id;
    }

    private function wilayahProvinsiId(string $provinsi): int
    {
        $kunci = 'provinsi|'.$provinsi.'|';
        if (isset($this->cacheWilayah[$kunci])) {
            return $this->cacheWilayah[$kunci];
        }

        $wilayah = Wilayah::firstOrCreate(
            ['level' => 'provinsi', 'provinsi' => $provinsi, 'kabupaten_kota' => null, 'nama_satuan' => null],
        );

        return $this->cacheWilayah[$kunci] = $wilayah->id;
    }

    // ------------------------------------------------------------------
    // Pembakuan nilai
    // ------------------------------------------------------------------

    private function bakukanLabel(string|int|float|null $nilai): string
    {
        $nilai = is_string($nilai) ? trim($nilai) : (string) ($nilai ?? '');

        return in_array($nilai, self::LABEL_SAH, true) ? $nilai : self::NILAI_KOSONG;
    }

    private function bakukanPerubahan(string|int|float|null $nilai): string
    {
        $nilai = is_string($nilai) ? trim($nilai) : (string) ($nilai ?? '');

        return in_array($nilai, self::PERUBAHAN_SAH, true) ? $nilai : self::NILAI_KOSONG;
    }

    // ------------------------------------------------------------------
    // Utilitas kecil
    // ------------------------------------------------------------------

    /**
     * @param  array<int, string|int|float|null>  $nilai
     */
    private function sel(array $nilai, ?int $kolom): ?string
    {
        if ($kolom === null) {
            return null;
        }
        $isi = $nilai[$kolom] ?? null;
        if ($isi === null) {
            return null;
        }
        $isi = is_string($isi) ? trim($isi) : (string) $isi;

        return $isi === '' ? null : $isi;
    }

    /**
     * @param  array<int, string|int|float|null>  $nilai
     */
    private function barisKosong(array $nilai): bool
    {
        foreach ($nilai as $sel) {
            if ($sel !== null && trim((string) $sel) !== '') {
                return false;
            }
        }

        return true;
    }
}

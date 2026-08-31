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
use RuntimeException;
use Throwable;

/**
 * Mengubah berkas Rapor Pendidikan tingkat satuan pendidikan menjadi baris
 * tabel `wilayah` (level `satuan`) dan `capaian`.
 *
 * PERINGATAN. Struktur berkas Rapor Pendidikan tingkat satuan pendidikan BELUM
 * pernah diuji tim dengan berkas asli (lihat PRD bagian 4.3). Parser ini
 * dibangun atas DUGAAN bahwa strukturnya menyerupai berkas daerah: header
 * bertingkat tiga baris dengan sel ter-merge, kolom dimensi 1-4
 * (Provinsi, Kabupaten/Kota, Jenis Satuan, Status), lalu pasangan kolom
 * label/perubahan per indikator. Bila berkas asli ternyata berbeda, parser
 * ini akan menolak berkas itu dengan pesan yang jelas, bukan mengarang data.
 *
 * Berbeda dari berkas daerah, berkas satuan berukuran kecil sehingga satu
 * sheet dimuat penuh. Baris irisan ("Berdasarkan Kelompok Gender" dsb) tidak
 * ada di ruang lingkup ini.
 *
 * Baris capaian "Tidak Tersedia" tidak disimpan, konsisten dengan
 * CapaianDaerahParser.
 */
class CapaianSekolahParser
{
    private const LABEL_SAH = ['Baik', 'Sedang', 'Kurang', 'Tidak Tersedia'];

    private const PERUBAHAN_SAH = ['Naik', 'Turun', 'Tidak berubah', 'Tidak Tersedia'];

    private const NILAI_KOSONG = 'Tidak Tersedia';

    /** Baris judul yang bukan nama satuan pendidikan. */
    private const AWALAN_ABAIKAN = ['berdasarkan', 'catatan', 'sumber'];

    /** @var array<string, int> cache wilayah dalam satu impor */
    private array $cacheWilayah = [];

    /** @var array<string, list<int>> */
    private array $indeksIndikator = [];

    public function __construct(private readonly HeaderResolver $headerResolver) {}

    /**
     * Impor berkas satuan pendidikan, membuat/menemukan catatan impor lewat
     * hash isi berkas (idempoten).
     */
    public function impor(string $path, ?string $namaSatuan = null): ImporBerkas
    {
        if (! is_file($path)) {
            throw new RuntimeException("Berkas tidak ditemukan: {$path}");
        }

        $impor = ImporBerkas::firstOrNew(['hash_berkas' => hash_file('sha256', $path)]);
        $this->imporKe($impor, $path, $namaSatuan);

        return $impor;
    }

    /**
     * Impor ke sebuah catatan impor yang sudah ada. Dipakai oleh
     * ProsesImporSekolah agar status berkas ("antre" -> "proses" -> "selesai")
     * terlihat di antarmuka sepanjang proses.
     */
    public function imporKe(ImporBerkas $impor, string $path, ?string $namaSatuan = null): void
    {
        if (! is_file($path)) {
            throw new RuntimeException("Berkas tidak ditemukan: {$path}");
        }

        if ($impor->exists && $impor->status === 'selesai') {
            return;
        }

        $impor->fill([
            'nama_berkas' => $impor->nama_berkas ?: basename($path),
            'jenis' => 'satuan',
            'hash_berkas' => $impor->hash_berkas ?: hash_file('sha256', $path),
            'status' => 'proses',
            'catatan_galat' => null,
        ])->save();

        Capaian::where('impor_id', $impor->id)->delete();
        $this->cacheWilayah = [];
        $this->indeksIndikator = [];

        try {
            $jumlah = $this->prosesBerkas($impor, $path, $namaSatuan);
        } catch (Throwable $e) {
            $impor->fill(['status' => 'gagal', 'catatan_galat' => $this->pesanGagal($e)])->save();
            throw $e;
        }

        $impor->fill([
            'status' => 'selesai',
            'jumlah_baris' => $jumlah,
            'diproses_pada' => now(),
        ])->save();
    }

    private function prosesBerkas(ImporBerkas $impor, string $path, ?string $namaSatuan): int
    {
        $reader = new Xlsx;
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $spreadsheet = $reader->load($path);

        $sheet = $spreadsheet->getSheet(0);
        $tertinggiKolom = $sheet->getHighestColumn();
        $tertinggiKolomIndeks = Coordinate::columnIndexFromString($tertinggiKolom);

        $awal = $sheet->rangeToArray("A1:{$tertinggiKolom}20", null, false, false, false);

        $barisJudulKolom = $this->cariBarisLabelCapaian($awal);
        if ($barisJudulKolom === null || $barisJudulKolom < 3) {
            throw new RuntimeException(
                'Berkas tidak dapat dibaca. Baris "Label Capaian" tidak ditemukan pada 20 baris pertama; '.
                'struktur tidak sesuai format Rapor Pendidikan satuan pendidikan.'
            );
        }

        // Header sama seperti berkas daerah: tiga baris tepat di atas dan pada
        // baris judul kolom itu sendiri.
        $b6 = $awal[$barisJudulKolom - 3] ?? [];
        $b7 = $awal[$barisJudulKolom - 2] ?? [];
        $b8 = $awal[$barisJudulKolom - 1] ?? [];

        $peta = $this->headerResolver->resolve($b6, $b7, $b8);
        $dimensi = $this->kolomDimensi($peta);
        $pasangan = $this->pasanganIndikator($peta);
        $kolomTerakhir = $peta === [] ? 4 : max(array_keys($peta));

        $nama = $namaSatuan ?? $this->namaSatuanDari(array_slice($awal, 0, $barisJudulKolom - 3));
        $tahun = $this->deteksiTahun($b8, $awal);

        $this->indeksIndikator = $this->bangunIndeksIndikator();

        $barisData = $sheet->rangeToArray(
            'A'.($barisJudulKolom + 1).':'.$tertinggiKolom.$sheet->getHighestRow(),
            null, false, false, true
        );

        $batch = [];
        $jumlah = 0;

        foreach ($barisData as $baris) {
            $nilai = [];
            foreach ($baris as $huruf => $isi) {
                $k = Coordinate::columnIndexFromString($huruf);
                if ($k > $kolomTerakhir) {
                    continue;
                }
                $nilai[$k] = is_string($isi) ? trim($isi) : $isi;
            }

            if ($this->barisKosong($nilai)) {
                continue;
            }

            $provinsi = $this->sel($nilai, $dimensi['provinsi'] ?? null);
            $kabkota = $this->sel($nilai, $dimensi['kabupaten_kota'] ?? null);
            $jenisSatuan = $this->sel($nilai, $dimensi['jenis_satuan'] ?? null) ?? '';
            $statusSatuan = $this->sel($nilai, $dimensi['status_satuan'] ?? null) ?? '';

            $wilayahId = $this->wilayahSatuanId($nama, $provinsi, $kabkota);
            $jenisLayanan = PemetaanJenisLayanan::dari($jenisSatuan);
            $jumlah++;

            foreach ($pasangan as $p) {
                $label = $this->bakukan($this->sel($nilai, $p['kolom_label']), self::LABEL_SAH);
                if ($label === self::NILAI_KOSONG) {
                    continue;
                }

                $indikatorId = $this->indikatorId($p['nomor'], $p['nama'], $jenisLayanan);
                if ($indikatorId === null) {
                    continue;
                }

                $batch[] = [
                    'impor_id' => $impor->id,
                    'wilayah_id' => $wilayahId,
                    'indikator_id' => $indikatorId,
                    'tahun' => $tahun,
                    'jenis_satuan' => mb_substr($jenisSatuan, 0, 64),
                    'status_satuan' => mb_substr($statusSatuan, 0, 32),
                    'label_capaian' => $label,
                    'perubahan_nilai' => $this->bakukan(
                        $p['kolom_perubahan'] !== null ? $this->sel($nilai, $p['kolom_perubahan']) : null,
                        self::PERUBAHAN_SAH
                    ),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if ($batch !== []) {
            foreach (array_chunk($batch, 500) as $potongan) {
                Capaian::insert($potongan);
            }
        }

        $spreadsheet->disconnectWorksheets();

        return $jumlah;
    }

    /**
     * @param  array<int, array<int, mixed>>  $baris
     */
    private function cariBarisLabelCapaian(array $baris): ?int
    {
        foreach ($baris as $i => $r) {
            foreach ($r as $sel) {
                if (is_string($sel) && stripos($sel, 'Label Capaian') !== false) {
                    return $i + 1; // 1-based
                }
            }
        }

        return null;
    }

    /**
     * Ambil nama satuan pendidikan dari baris judul. Format dugaan:
     * "DATA HASIL RAPOR PENDIDIKAN 2025 - SD NEGERI PERCOBAAN SURABAYA (NPSN ...)".
     *
     * @param  array<int, array<int, mixed>>  $barisJudul
     */
    private function namaSatuanDari(array $barisJudul): string
    {
        foreach ($barisJudul as $r) {
            foreach ($r as $sel) {
                if (! is_string($sel)) {
                    continue;
                }
                $teks = trim(preg_replace('/\s+/u', ' ', $sel) ?? '');
                if ($teks === '') {
                    continue;
                }
                $awalan = mb_strtolower(mb_substr($teks, 0, 12));
                foreach (self::AWALAN_ABAIKAN as $abai) {
                    if (str_starts_with($awalan, $abai)) {
                        continue 2;
                    }
                }

                // Ambil bagian setelah tanda pisah " - " bila ada.
                if (str_contains($teks, ' - ')) {
                    $teks = trim(substr($teks, strrpos($teks, ' - ') + 3));
                }

                return mb_substr($teks, 0, 160);
            }
        }

        return 'Satuan pendidikan';
    }

    /**
     * @param  array<int, mixed>  $b8
     * @param  array<int, array<int, mixed>>  $awal
     */
    private function deteksiTahun(array $b8, array $awal): int
    {
        foreach ([$b8, ...$awal] as $baris) {
            foreach ($baris as $sel) {
                if (is_string($sel) && preg_match('/(20\d{2})/', $sel, $m)) {
                    return (int) $m[1];
                }
            }
        }

        return (int) date('Y');
    }

    /**
     * @param  array<int, array<string, string|null>>  $peta
     * @return array<string, int>
     */
    private function kolomDimensi(array $peta): array
    {
        $out = [];
        foreach ($peta as $kolom => $entri) {
            if (($entri['jenis'] ?? null) === 'dimensi') {
                $out[$entri['dimensi']] = $kolom;
            }
        }

        return $out;
    }

    /**
     * @param  array<int, array<string, string|null>>  $peta
     * @return list<array{nomor:string, nama:string, kolom_label:int, kolom_perubahan:int|null}>
     */
    private function pasanganIndikator(array $peta): array
    {
        ksort($peta);
        $pasangan = [];
        $tertunda = null;

        foreach ($peta as $kolom => $entri) {
            if (($entri['jenis'] ?? null) !== 'indikator') {
                continue;
            }

            if ($entri['peran'] === 'label') {
                if ($tertunda !== null) {
                    $pasangan[] = $tertunda + ['kolom_perubahan' => null];
                }
                $tertunda = ['nomor' => $entri['nomor'], 'nama' => $entri['nama'], 'kolom_label' => $kolom];

                continue;
            }

            if ($tertunda !== null && $tertunda['nomor'] === $entri['nomor']) {
                $pasangan[] = $tertunda + ['kolom_perubahan' => $kolom];
                $tertunda = null;
            }
        }

        if ($tertunda !== null) {
            $pasangan[] = $tertunda + ['kolom_perubahan' => null];
        }

        return $pasangan;
    }

    /**
     * @return array<string, list<int>>
     */
    private function bangunIndeksIndikator(): array
    {
        $out = [];
        Indikator::query()->select(['id', 'nomor', 'nama', 'jenis_layanan'])->orderBy('id')
            ->chunk(500, function ($kelompok) use (&$out) {
                foreach ($kelompok as $i) {
                    $out['nn:'.$i->nomor.'|'.$i->nama][] = $i->id;
                    $out['nj:'.$i->nomor.'|'.$i->jenis_layanan][] = $i->id;
                    $out['n:'.$i->nomor][] = $i->id;
                }
            });

        return $out;
    }

    private function indikatorId(string $nomor, string $nama, string $jenisLayanan): ?int
    {
        foreach (['nn:'.$nomor.'|'.$nama, 'nj:'.$nomor.'|'.$jenisLayanan, 'n:'.$nomor] as $kunci) {
            $kandidat = $this->indeksIndikator[$kunci] ?? [];
            if (count($kandidat) === 1) {
                return $kandidat[0];
            }
            if (str_starts_with($kunci, 'nn:') && $kandidat !== []) {
                return $kandidat[0];
            }
        }

        return null;
    }

    private function wilayahSatuanId(string $namaSatuan, ?string $provinsi, ?string $kabkota): int
    {
        $kunci = $namaSatuan.'|'.($provinsi ?? '').'|'.($kabkota ?? '');
        if (isset($this->cacheWilayah[$kunci])) {
            return $this->cacheWilayah[$kunci];
        }

        $indukId = null;
        if ($provinsi !== null && $provinsi !== '') {
            $prov = Wilayah::firstOrCreate([
                'level' => 'provinsi', 'provinsi' => $provinsi, 'kabupaten_kota' => null, 'nama_satuan' => null,
            ]);
            $indukId = $prov->id;

            if ($kabkota !== null && $kabkota !== '' && $kabkota !== '-') {
                $kab = Wilayah::firstOrCreate(
                    ['level' => 'kabkota', 'provinsi' => $provinsi, 'kabupaten_kota' => $kabkota, 'nama_satuan' => null],
                    ['induk_id' => $prov->id],
                );
                $indukId = $kab->id;
            }
        }

        $satuan = Wilayah::firstOrCreate(
            [
                'level' => 'satuan',
                'provinsi' => $provinsi,
                'kabupaten_kota' => $kabkota,
                'nama_satuan' => $namaSatuan,
            ],
            ['induk_id' => $indukId],
        );

        return $this->cacheWilayah[$kunci] = $satuan->id;
    }

    /**
     * @param  list<string>  $sah
     */
    private function bakukan(string|int|float|null $nilai, array $sah): string
    {
        $nilai = is_string($nilai) ? trim($nilai) : (string) ($nilai ?? '');

        return in_array($nilai, $sah, true) ? $nilai : self::NILAI_KOSONG;
    }

    /**
     * @param  array<int, mixed>  $nilai
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
     * @param  array<int, mixed>  $nilai
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

    private function pesanGagal(Throwable $e): string
    {
        return 'Berkas tidak dapat dibaca. '.$e->getMessage();
    }
}

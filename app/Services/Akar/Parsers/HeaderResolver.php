<?php

declare(strict_types=1);

namespace App\Services\Akar\Parsers;

use RuntimeException;

/**
 * Merekonstruksi header bertingkat sheet provinsi (baris 6-8, sel ter-merge)
 * menjadi peta kolom. Komponen paling kritis dari alur impor. Lihat ARCHITECTURE.md
 * 3.2 dan 6.1.
 *
 *   Baris 6  kode indikator induk : "A.1", "A.2"
 *   Baris 7  nama lengkap          : "A.1 Kemampuan literasi", "A.1.1 ..."
 *   Baris 8  nama kolom            : "Provinsi", ..., "Label Capaian 2025", "Perubahan ..."
 *
 * Susunan indikator berbeda antaredisi, jadi resolusi berdasarkan pola nama di
 * baris 7, bukan posisi kolom. Kolom 1-4 dimensi; 5+ berpasangan label/perubahan.
 * Nomor indikator sama bisa muncul >1x dengan nama berbeda (PAUD vs dasar-menengah),
 * jadi nama ikut dikembalikan sebagai kunci disambiguasi.
 */
class HeaderResolver
{
    /** Nomor indikator: huruf A-E + segmen angka. Cocok: A.1, A.1.1, D.17. */
    private const POLA_NOMOR = '/^([A-E]\.\d+(?:\.\d+)*)(?:\s+(.*))?$/us';

    /**
     * @param  list<int|float|string|null>  $baris6  isi mentah baris 6, indeks 0 = kolom A
     * @param  list<int|float|string|null>  $baris7  isi mentah baris 7
     * @param  list<int|float|string|null>  $baris8  isi mentah baris 8
     * @return array<int, array<string, string|null>> peta dari nomor kolom (1-based) ke atribut kolom
     *
     * Bentuk nilai:
     *   Dimensi   : ['jenis' => 'dimensi', 'dimensi' => 'provinsi'|'kabupaten_kota'|'jenis_satuan'|'status_satuan']
     *   Indikator : ['jenis' => 'indikator', 'nomor' => 'D.2', 'nama' => 'Refleksi ...',
     *                'induk' => 'D.2'|null, 'peran' => 'label'|'perubahan']
     */
    public function resolve(array $baris6, array $baris7, array $baris8): array
    {
        $b6 = $this->isiMaju($this->bersihkan($baris6));
        $b7 = $this->isiMaju($this->bersihkan($baris7));
        $b8 = $this->bersihkan($baris8);

        $jumlahKolom = max(count($b6), count($b7), count($b8));
        if ($jumlahKolom < 6) {
            throw new RuntimeException(
                'Header sheet provinsi tidak lengkap: hanya ditemukan '.$jumlahKolom.' kolom.'
            );
        }

        $peta = [];
        for ($i = 0; $i < $jumlahKolom; $i++) {
            $nomorKolom = $i + 1;

            if ($nomorKolom <= 4) {
                $peta[$nomorKolom] = $this->kolomDimensi($nomorKolom, $b8[$i] ?? null);

                continue;
            }

            $entri = $this->kolomIndikator($nomorKolom, $b6[$i] ?? null, $b7[$i] ?? null, $b8[$i] ?? null);
            if ($entri !== null) {
                $peta[$nomorKolom] = $entri;
            }
        }

        $this->pastikanAdaIndikator($peta);

        return $peta;
    }

    /**
     * Kolom 1-4 dimensi. Peran dikenali dari teks baris 8 dulu (tahan perbedaan
     * antaredisi), jatuh ke posisi bila teks tak dikenali.
     *
     * @return array<string, string>
     */
    private function kolomDimensi(int $nomorKolom, ?string $judul): array
    {
        $teks = mb_strtolower((string) $judul);

        $dimensi = match (true) {
            str_contains($teks, 'kabupaten') || str_contains($teks, 'kota') => 'kabupaten_kota',
            str_contains($teks, 'jenis') && str_contains($teks, 'satuan') => 'jenis_satuan',
            str_contains($teks, 'status') && str_contains($teks, 'satuan') => 'status_satuan',
            str_contains($teks, 'provinsi') => 'provinsi',
            default => [1 => 'provinsi', 2 => 'kabupaten_kota', 3 => 'jenis_satuan', 4 => 'status_satuan'][$nomorKolom],
        };

        return ['jenis' => 'dimensi', 'dimensi' => $dimensi];
    }

    /**
     * Kolom 5+ berisi pasangan label/perubahan per indikator.
     *
     * @return array<string, string|null>|null null bila kolom benar-benar kosong (kolom sisa di ujung sheet)
     */
    private function kolomIndikator(int $nomorKolom, ?string $induk, ?string $nama, ?string $judulKolom): ?array
    {
        $adaNama = $nama !== null && $nama !== '';
        $adaJudul = $judulKolom !== null && $judulKolom !== '';

        // Kolom data indikator selalu berjudul "Label Capaian"/"Perubahan ..." di
        // baris 8. Tanpa judul = sisa lebar sheet, diabaikan. Cek judul dulu karena
        // forward-fill baris 7 bisa merembetkan nama indikator terakhir ke sana.
        if (! $adaJudul) {
            return null;
        }

        if (! $adaNama) {
            throw new RuntimeException(
                "Kolom {$nomorKolom} sheet provinsi memiliki judul '{$judulKolom}' tetapi baris 7 ".
                'tidak memuat nama indikator. Struktur berkas tidak sesuai format Rapor Pendidikan.'
            );
        }

        if (! preg_match(self::POLA_NOMOR, $nama, $cocok)) {
            throw new RuntimeException(
                "Kolom {$nomorKolom} sheet provinsi: baris 7 berisi '{$nama}' yang tidak diawali ".
                'nomor indikator berpola [A-E].n. Struktur berkas tidak sesuai format Rapor Pendidikan.'
            );
        }

        $nomor = $cocok[1];
        $namaIndikator = isset($cocok[2]) ? trim($cocok[2]) : '';

        $peran = $this->peranKolom($nomorKolom, $judulKolom);

        return [
            'jenis' => 'indikator',
            'nomor' => $nomor,
            'nama' => $namaIndikator !== '' ? $namaIndikator : $nomor,
            'induk' => ($induk !== null && $induk !== '' && $induk !== $nomor) ? $induk : null,
            'peran' => $peran,
        ];
    }

    private function peranKolom(int $nomorKolom, ?string $judulKolom): string
    {
        $teks = mb_strtolower((string) $judulKolom);

        return match (true) {
            str_contains($teks, 'label capaian') => 'label',
            str_contains($teks, 'perubahan') => 'perubahan',
            default => throw new RuntimeException(
                "Kolom {$nomorKolom} sheet provinsi memiliki judul '{$judulKolom}' yang bukan ".
                "'Label Capaian' maupun 'Perubahan Nilai Capaian'."
            ),
        };
    }

    /**
     * @param  array<int, array<string, string|null>>  $peta
     */
    private function pastikanAdaIndikator(array $peta): void
    {
        foreach ($peta as $entri) {
            if (($entri['jenis'] ?? null) === 'indikator') {
                return;
            }
        }

        throw new RuntimeException(
            'Tidak ada satu pun kolom indikator yang dikenali di header sheet provinsi.'
        );
    }

    /**
     * Sel -> string ter-trim (whitespace runtuh jadi satu spasi); kosong -> null.
     *
     * @param  list<int|float|string|null>  $baris
     * @return list<string|null>
     */
    private function bersihkan(array $baris): array
    {
        return array_map(static function ($sel): ?string {
            if ($sel === null) {
                return null;
            }
            $bersih = trim(preg_replace('/\s+/u', ' ', (string) $sel) ?? '');

            return $bersih === '' ? null : $bersih;
        }, array_values($baris));
    }

    /**
     * Forward-fill: isi sel kosong bekas merge dengan nilai tak kosong di kirinya.
     *
     * @param  list<string|null>  $baris
     * @return list<string|null>
     */
    private function isiMaju(array $baris): array
    {
        $terakhir = null;
        foreach ($baris as $i => $sel) {
            if ($sel !== null) {
                $terakhir = $sel;
            } else {
                $baris[$i] = $terakhir;
            }
        }

        return $baris;
    }
}

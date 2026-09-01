<?php

declare(strict_types=1);

namespace App\Services\Akar\Parsers;

use RuntimeException;

class HeaderResolver
{
    private const POLA_NOMOR = '/^([A-E]\.\d+(?:\.\d+)*)(?:\s+(.*))?$/us';

    /**
     * @param  list<int|float|string|null>  $baris6  isi mentah baris 6, indeks 0 = kolom A
     * @param  list<int|float|string|null>  $baris7  isi mentah baris 7
     * @param  list<int|float|string|null>  $baris8  isi mentah baris 8
     * @return array<int, array<string, string|null>> peta nomor kolom (1-based) => atribut kolom
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

    /** @return array<string, string> */
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

    /** @return array<string, string|null>|null null bila kolom benar-benar kosong (kolom sisa di ujung sheet) */
    private function kolomIndikator(int $nomorKolom, ?string $induk, ?string $nama, ?string $judulKolom): ?array
    {
        $adaNama = $nama !== null && $nama !== '';
        $adaJudul = $judulKolom !== null && $judulKolom !== '';

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

    /** @param  array<int, array<string, string|null>>  $peta */
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

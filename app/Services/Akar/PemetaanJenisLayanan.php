<?php

declare(strict_types=1);

namespace App\Services\Akar;

/**
 * Memetakan nilai "Jenis Satuan Pendidikan" pada sheet provinsi (mis. "SD Umum",
 * "SMK Umum", "PAUD") ke salah satu dari tiga jenis layanan yang dipakai berkas
 * Metadata indikator: "Pendidikan Dasar dan Pendidikan Menengah", "Pendidikan
 * Anak Usia Dini", atau "Vokasional".
 *
 * Pemetaan ini menentukan kumpulan indikator mana yang berlaku untuk sebuah
 * baris capaian, sehingga dipakai baik oleh parser (saat impor) maupun lapisan
 * analisis (saat menyusun profil). Dipisah ke satu tempat agar keduanya tidak
 * pernah berbeda aturan.
 */
final class PemetaanJenisLayanan
{
    public const DASAR_MENENGAH = 'Pendidikan Dasar dan Pendidikan Menengah';

    public const PAUD = 'Pendidikan Anak Usia Dini';

    public const VOKASIONAL = 'Vokasional';

    public static function dari(string $jenisSatuan): string
    {
        $teks = mb_strtolower(trim($jenisSatuan));

        return match (true) {
            str_starts_with($teks, 'paud'),
            $teks === 'ra',
            str_contains($teks, 'anak usia dini') => self::PAUD,

            str_contains($teks, 'smk') => self::VOKASIONAL,

            default => self::DASAR_MENENGAH,
        };
    }
}

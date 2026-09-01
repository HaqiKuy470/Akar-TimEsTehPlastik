<?php

declare(strict_types=1);

namespace App\Services\Akar;

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

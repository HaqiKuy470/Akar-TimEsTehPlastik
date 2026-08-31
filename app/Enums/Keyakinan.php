<?php

namespace App\Enums;

/**
 * Tingkat keyakinan hasil analisis akar masalah.
 *
 * Mengikuti tabel keyakinan di ARCHITECTURE.md §6.3: makin banyak indikator
 * pendukung yang berlabel "Kurang", makin kuat keyakinan bahwa kandidat akar
 * masalah itu benar. Bila seluruh pendukung "Baik" atau "Tidak Tersedia",
 * sistem menyatakan bukti tidak cukup, bukan memaksakan kesimpulan.
 */
enum Keyakinan: string
{
    case Kuat = 'kuat';
    case Sedang = 'sedang';
    case Lemah = 'lemah';
    case TidakCukupBukti = 'tidak_cukup_bukti';

    /**
     * Label siap tampil untuk pengguna.
     */
    public function label(): string
    {
        return match ($this) {
            self::Kuat => 'Bukti kuat',
            self::Sedang => 'Bukti sedang',
            self::Lemah => 'Bukti lemah',
            self::TidakCukupBukti => 'Bukti tidak cukup',
        };
    }
}

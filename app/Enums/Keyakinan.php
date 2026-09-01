<?php

namespace App\Enums;

enum Keyakinan: string
{
    case Kuat = 'kuat';
    case Sedang = 'sedang';
    case Lemah = 'lemah';
    case TidakCukupBukti = 'tidak_cukup_bukti';

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

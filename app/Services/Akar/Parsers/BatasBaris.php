<?php

declare(strict_types=1);

namespace App\Services\Akar\Parsers;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Filter pembaca PhpSpreadsheet yang membatasi pembacaan pada rentang baris
 * tertentu. Dipakai saat hanya perlu membaca baris header untuk mendeteksi
 * tahun edisi, tanpa memuat seluruh 951 baris data.
 */
class BatasBaris implements IReadFilter
{
    public function __construct(
        private readonly int $barisAwal,
        private readonly int $barisAkhir,
    ) {}

    public function readCell(string $column, int $row, string $worksheetName = ''): bool
    {
        return $row >= $this->barisAwal && $row <= $this->barisAkhir;
    }
}

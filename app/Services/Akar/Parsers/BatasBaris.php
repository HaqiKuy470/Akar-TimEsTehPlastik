<?php

declare(strict_types=1);

namespace App\Services\Akar\Parsers;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

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

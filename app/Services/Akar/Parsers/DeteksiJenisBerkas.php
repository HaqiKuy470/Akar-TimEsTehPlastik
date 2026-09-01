<?php

declare(strict_types=1);

namespace App\Services\Akar\Parsers;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Throwable;

final class DeteksiJenisBerkas
{
    public const DAERAH = 'daerah';

    public const SATUAN = 'satuan';

    public const TIDAK_DIKENAL = 'tidak_dikenal';

    private const SHEET_BUKAN_WILAYAH = ['Metadata', 'Nasional'];

    public function untuk(string $path): string
    {
        if (! is_file($path)) {
            return self::TIDAK_DIKENAL;
        }

        try {
            $sheets = (new Xlsx)->listWorksheetNames($path);
        } catch (Throwable) {
            return self::TIDAK_DIKENAL;
        }

        $adaMetadata = in_array('Metadata', $sheets, true);
        $kandidat = array_values(array_diff($sheets, self::SHEET_BUKAN_WILAYAH));
        if ($kandidat === []) {
            return self::TIDAK_DIKENAL;
        }

        $adaHeaderCapaian = false;
        foreach (array_slice($kandidat, 0, 3) as $sheet) {
            if ($this->sheetPunyaHeaderCapaian($path, $sheet)) {
                $adaHeaderCapaian = true;
                break;
            }
        }

        if (! $adaHeaderCapaian) {
            return self::TIDAK_DIKENAL;
        }

        return $adaMetadata ? self::DAERAH : self::SATUAN;
    }

    private function sheetPunyaHeaderCapaian(string $path, string $sheet): bool
    {
        $reader = new Xlsx;
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([$sheet]);
        $reader->setReadFilter(new BatasBaris(1, 15));

        try {
            $ws = $reader->load($path)->getSheetByName($sheet);
        } catch (Throwable) {
            return false;
        }

        if ($ws === null) {
            return false;
        }

        $tertinggi = $ws->getHighestColumn();
        $baris = $ws->rangeToArray("A1:{$tertinggi}15", null, false, false, false);

        foreach ($baris as $r) {
            foreach ($r as $sel) {
                if (is_string($sel) && stripos($sel, 'Label Capaian') !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}

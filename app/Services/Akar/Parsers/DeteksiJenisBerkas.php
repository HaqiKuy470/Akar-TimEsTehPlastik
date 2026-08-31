<?php

declare(strict_types=1);

namespace App\Services\Akar\Parsers;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Throwable;

/**
 * Menebak jenis berkas Rapor Pendidikan yang diunggah pengguna: tingkat daerah
 * (Data Rapor Pendidikan Indonesia) atau tingkat satuan pendidikan.
 *
 * Pembeda utama: berkas daerah memuat sheet `Metadata` dan puluhan sheet
 * wilayah. Berkas satuan pendidikan tidak memuat sheet `Metadata`.
 *
 * CATATAN. Struktur berkas Rapor Pendidikan tingkat satuan pendidikan BELUM
 * pernah diverifikasi dengan berkas asli oleh tim. Deteksi ini konservatif:
 * bila sebuah berkas tidak memiliki ciri header capaian yang jelas, ia
 * dikembalikan sebagai `tidak_dikenal` dan ditolak dengan pesan yang jujur,
 * bukan dipaksa diproses.
 */
final class DeteksiJenisBerkas
{
    public const DAERAH = 'daerah';

    public const SATUAN = 'satuan';

    public const TIDAK_DIKENAL = 'tidak_dikenal';

    /** Sheet yang bukan sheet wilayah pada berkas daerah. */
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

        // Sebuah sheet capaian selalu memuat teks "Label Capaian" di baris
        // header. Tanpa itu, berkas ini bukan Rapor Pendidikan yang kita kenal.
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

<?php

/**
 * Pembuat berkas fixture untuk mode satuan pendidikan (F10).
 *
 * PENTING: struktur berkas Rapor Pendidikan tingkat satuan pendidikan BELUM
 * diverifikasi tim (tidak ada berkas contoh). Fixture ini mewakili DUGAAN kerja
 * kami: strukturnya menyerupai berkas daerah (sistem sumber yang sama) namun
 * memuat satu satuan pendidikan, dengan namanya di baris judul. Bila kelak
 * diperoleh berkas asli dan strukturnya berbeda, sesuaikan parser + fixture ini.
 *
 * Menghasilkan dua berkas:
 *   berkas_sekolah_mini.xlsx  — berkas satuan pendidikan yang sah (dugaan)
 *   berkas_tak_dikenal.xlsx   — berkas .xlsx yang bukan Rapor Pendidikan
 *
 * Jalankan ulang bila strukturnya perlu berubah:
 *   php tests/Fixtures/buat_berkas_sekolah_mini.php
 */

require __DIR__.'/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// ---------------------------------------------------------------------------
// 1. Berkas satuan pendidikan (dugaan struktur)
// ---------------------------------------------------------------------------
$s = new Spreadsheet;
$sheet = $s->getActiveSheet()->setTitle('Rapor Pendidikan');

$sheet->setCellValue('A1', 'DATA HASIL RAPOR PENDIDIKAN 2025 - SD NEGERI PERCOBAAN SURABAYA (NPSN 20500001)');
$sheet->setCellValue('A2', 'Berdasarkan data pendidikan 2024 (terakhir diperbarui 20 Agustus 2025)');
$sheet->setCellValue('A4', 'Catatan: nilai capaian merupakan agregat satuan pendidikan.');

// Baris 6: kode indikator induk, ter-merge sepanjang kelompoknya.
$sheet->setCellValue('E6', 'A.1');
$sheet->mergeCells('E6:H6');
$sheet->setCellValue('I6', 'D.4');
$sheet->mergeCells('I6:J6');

// Baris 7: nama indikator lengkap.
$sheet->setCellValue('E7', 'A.1 Kemampuan literasi');
$sheet->mergeCells('E7:F7');
$sheet->setCellValue('G7', 'A.1.1 Kompetensi membaca teks informasi');
$sheet->mergeCells('G7:H7');
$sheet->setCellValue('I7', 'D.4 Iklim keamanan satuan pendidikan');
$sheet->mergeCells('I7:J7');

// Baris 8: nama kolom. Dimensi 1-4 sama seperti berkas daerah.
$sheet->setCellValue('A8', 'Provinsi');
$sheet->setCellValue('B8', 'Kabupaten/Kota');
$sheet->setCellValue('C8', "Jenis Satuan Pendidikan\n");
$sheet->setCellValue('D8', "Status Satuan Pendidikan\n");
foreach (['E', 'G', 'I'] as $k) {
    $sheet->setCellValue($k.'8', 'Label Capaian 2025');
}
foreach (['F', 'H', 'J'] as $k) {
    $sheet->setCellValue($k.'8', 'Perubahan Nilai Capaian dari Tahun Lalu');
}

// Baris 9+: data. Kolom: Provinsi, Kab/Kota, Jenis, Status,
// A.1(label,perubahan), A.1.1(label,perubahan), D.4(label,perubahan)
$data = [
    ['Jawa Timur', 'Kota Surabaya', 'SD Umum', 'Negeri', 'Kurang', 'Turun', 'Sedang', 'Naik', 'Baik', 'Naik'],
    ['Jawa Timur', 'Kota Surabaya', 'SD Umum', 'Semua (Negeri dan Swasta)', 'Sedang', 'Tidak berubah', 'Sedang', 'Naik', 'Baik', 'Tidak berubah'],
    ['', '', '', '', '', '', '', '', '', ''],
];
$r = 9;
foreach ($data as $row) {
    $col = 'A';
    foreach ($row as $v) {
        $sheet->setCellValue($col.$r, $v);
        $col++;
    }
    $r++;
}

(new Xlsx($s))->save(__DIR__.'/berkas_sekolah_mini.xlsx');
echo "Fixture ditulis: berkas_sekolah_mini.xlsx\n";

// ---------------------------------------------------------------------------
// 2. Berkas yang bukan Rapor Pendidikan sama sekali
// ---------------------------------------------------------------------------
$t = new Spreadsheet;
$ts = $t->getActiveSheet()->setTitle('Sheet1');
$ts->fromArray([['Nama', 'Nilai'], ['Contoh', 123], ['Lain', 456]]);
(new Xlsx($t))->save(__DIR__.'/berkas_tak_dikenal.xlsx');
echo "Fixture ditulis: berkas_tak_dikenal.xlsx\n";

<?php

/**
 * Pembuat berkas fixture `sheet_provinsi_mini.xlsx`.
 *
 * Fixture ini meniru struktur Data Rapor Pendidikan Indonesia dalam ukuran
 * kecil: header bertingkat tiga baris dengan sel ter-merge di baris 6-7, kolom
 * dimensi 1-4, lalu pasangan kolom label/perubahan per indikator. Dipakai oleh
 * CapaianDaerahParserTest.
 *
 * Jalankan ulang bila strukturnya perlu berubah:
 *   php tests/Fixtures/buat_sheet_provinsi_mini.php
 */

require __DIR__.'/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet;
$spreadsheet->removeSheetByIndex(0);

// Sheet non-provinsi yang harus diabaikan parser.
$spreadsheet->createSheet()->setTitle('Metadata')->setCellValue('A1', 'Metadata dummy');
$spreadsheet->createSheet()->setTitle('Nasional')->setCellValue('A1', 'Nasional dummy');

/**
 * Isi satu sheet provinsi.
 *
 * @param  list<array{0:string,1:string,2:string,3:string,4:string,5:string,6:string,7:string,8:string,9:string}>  $dataRows
 */
function isiSheetProvinsi(Spreadsheet $spreadsheet, string $provinsi, array $dataRows): void
{
    $sheet = $spreadsheet->createSheet()->setTitle($provinsi);

    $sheet->setCellValue('A1', "DATA HASIL RAPOR PENDIDIKAN 2025 (Prov. {$provinsi} dan kabupaten/kota di bawahnya)");
    $sheet->setCellValue('A2', 'Berdasarkan data pendidikan 2024 (terakhir diperbarui 20 Agustus 2025)');
    $sheet->setCellValue('A4', 'Catatan: untuk Rapor Pendidikan Indonesia, nilai capaian hanya tersedia di level agregat.');

    // Baris 6: kode indikator induk, ter-merge sepanjang kelompoknya.
    $sheet->setCellValue('E6', 'A.1');
    $sheet->mergeCells('E6:H6');          // A.1 + A.1.1
    $sheet->setCellValue('I6', 'D.2');
    $sheet->mergeCells('I6:L6');          // D.2 (dua varian)

    // Baris 7: nama indikator lengkap, ter-merge sepanjang pasangan kolomnya.
    $sheet->setCellValue('E7', 'A.1 Kemampuan literasi');
    $sheet->mergeCells('E7:F7');
    $sheet->setCellValue('G7', 'A.1.1 Kompetensi membaca teks informasi');
    $sheet->mergeCells('G7:H7');
    $sheet->setCellValue('I7', 'D.2 Refleksi dan perbaikan pembelajaran oleh guru'); // varian dasar-menengah
    $sheet->mergeCells('I7:J7');
    $sheet->setCellValue('K7', 'D.2 Proses belajar yang sesuai bagi anak usia dini'); // varian PAUD
    $sheet->mergeCells('K7:L7');

    // Baris 8: nama kolom. Perhatikan newline di judul dimensi seperti berkas asli.
    $sheet->setCellValue('A8', 'Provinsi');
    $sheet->setCellValue('B8', 'Kabupaten/Kota');
    $sheet->setCellValue('C8', "Jenis Satuan Pendidikan\n");
    $sheet->setCellValue('D8', "Status Satuan Pendidikan\n");
    foreach (['E', 'G', 'I', 'K'] as $kolomLabel) {
        $sheet->setCellValue($kolomLabel.'8', 'Label Capaian 2025');
    }
    foreach (['F', 'H', 'J', 'L'] as $kolomPerubahan) {
        $sheet->setCellValue($kolomPerubahan.'8', 'Perubahan Nilai Capaian dari Tahun Lalu');
    }

    // Baris 9+: data.
    $r = 9;
    foreach ($dataRows as $baris) {
        $kolom = 'A';
        foreach ($baris as $nilai) {
            $sheet->setCellValue($kolom.$r, $nilai);
            $kolom++;
        }
        $r++;
    }
}

// Kolom data: Provinsi, Kab/Kota, Jenis Satuan, Status, lalu
// A.1(label,perubahan), A.1.1(label,perubahan), D.2-dasmen(label,perubahan), D.2-paud(label,perubahan)
isiSheetProvinsi($spreadsheet, 'Jawa Timur', [
    // Agregat provinsi (Kabupaten/Kota = "-")
    ['Jawa Timur', '-', 'SD Umum', 'Semua (Negeri dan Swasta)',
        'Kurang', 'Turun', 'Sedang', 'Naik', 'Kurang', 'Turun', 'Tidak Tersedia', 'Tidak Tersedia'],
    ['Jawa Timur', 'Kabupaten Malang', 'SD Umum', 'Semua (Negeri dan Swasta)',
        'Sedang', 'Naik', 'Baik', 'Naik', 'Sedang', 'Tidak berubah', 'Tidak Tersedia', 'Tidak Tersedia'],
    ['Jawa Timur', 'Kota Surabaya', 'SMP Umum', 'Semua (Negeri dan Swasta)',
        'Baik', 'Naik', 'Tidak Tersedia', 'Tidak Tersedia', 'Baik', 'Naik', 'Tidak Tersedia', 'Tidak Tersedia'],
    // Baris PAUD: kolom D.2 varian PAUD terisi, varian dasar-menengah "Tidak Tersedia"
    ['Jawa Timur', 'Kabupaten Gresik', 'PAUD', 'Semua (Negeri dan Swasta)',
        'Tidak Tersedia', 'Tidak Tersedia', 'Tidak Tersedia', 'Tidak Tersedia', 'Tidak Tersedia', 'Tidak Tersedia', 'Kurang', 'Turun'],
    // Baris kosong di tengah untuk menguji pelewatan
    ['', '', '', '', '', '', '', '', '', '', '', ''],
]);

isiSheetProvinsi($spreadsheet, 'Bali', [
    ['Bali', '-', 'SD Umum', 'Semua (Negeri dan Swasta)',
        'Baik', 'Naik', 'Baik', 'Naik', 'Baik', 'Naik', 'Tidak Tersedia', 'Tidak Tersedia'],
    ['Bali', 'Kabupaten Badung', 'SD Umum', 'Semua (Negeri dan Swasta)',
        'Baik', 'Tidak berubah', 'Sedang', 'Turun', 'Baik', 'Naik', 'Tidak Tersedia', 'Tidak Tersedia'],
]);

$tujuan = __DIR__.'/sheet_provinsi_mini.xlsx';
(new Xlsx($spreadsheet))->save($tujuan);
echo "Fixture ditulis: {$tujuan}\n";

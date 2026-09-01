<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Konfigurasi analisis AKAR
|--------------------------------------------------------------------------
|
| Seluruh aturan bisnis skor prioritas disimpan di sini, bukan di kode.
| Bila indikator atau ambang berubah tahun depan, ubah berkas ini, bukan
| kelas PrioritasCalculator. Nilai bobot disalin ke kolom
| analisis.bobot_dipakai setiap analisis dijalankan supaya hasil lama
| tetap dapat direproduksi.
|
| Rumus (lihat ARCHITECTURE.md bagian 6.2):
|   Skor = (bobot_komponen.label     x nilai_label)
|        + (bobot_komponen.perubahan x nilai_perubahan)
|        + (bobot_komponen.posisi    x nilai_posisi)
|        + (bobot_komponen.turunan   x nilai_turunan)
|
| Setiap nilai_* berada pada rentang 0..1, sehingga skor maksimum adalah
| jumlah seluruh bobot_komponen, yaitu 100.
*/

return [

    // Bobot tiap komponen terhadap skor akhir. Jumlahnya sebaiknya 100.
    'bobot_komponen' => [
        'label' => 40,
        'perubahan' => 25,
        'posisi' => 20,
        'turunan' => 15,
    ],

    // Kontribusi label capaian. Indikator "Tidak Tersedia" dikecualikan
    // dari perhitungan, tidak diberi nilai nol.
    'nilai_label' => [
        'Kurang' => 1.0,
        'Sedang' => 0.5,
        'Baik' => 0.0,
    ],

    // Kontribusi arah perubahan nilai capaian.
    'nilai_perubahan' => [
        'Turun' => 1.0,
        'Tidak berubah' => 0.5,
        'Naik' => 0.0,
        'Tidak Tersedia' => 0.0,
    ],

    // Label yang dianggap bermasalah saat menghitung dampak ke indikator
    // turunan (proporsi anak berlabel Kurang atau Sedang).
    'label_bermasalah' => ['Kurang', 'Sedang'],

    // Urutan mutu label capaian, dipakai saat memeringkat sebuah wilayah
    // terhadap wilayah lain (komponen "posisi relatif" skor prioritas).
    // Angka lebih besar berarti lebih baik. "Tidak Tersedia" tidak diberi
    // peringkat: wilayah tanpa data dikeluarkan dari populasi pemeringkatan,
    // bukan ditempatkan di posisi terbawah.
    'peringkat_label' => [
        'Baik' => 3,
        'Sedang' => 2,
        'Kurang' => 1,
    ],

    // Label yang dihitung sebagai "merah" dan "kuning" pada ringkasan profil.
    'label_merah' => ['Kurang'],
    'label_kuning' => ['Sedang'],
    'label_hijau' => ['Baik'],

    // Nilai yang menandai data tidak tersedia di berkas sumber.
    'nilai_kosong' => 'Tidak Tersedia',

    // Edisi berkas Rapor Pendidikan yang diimpor ke produksi. Edisi lama
    // hanya dipakai bila fitur tren (F6) dikerjakan.
    'edisi_produksi' => [2024, 2025],

    // Nama lengkap tiap dimensi indikator (huruf awal nomor indikator).
    // Dipakai untuk mengelompokkan indikator pada profil capaian daerah.
    'dimensi' => [
        'A' => 'Mutu dan relevansi hasil belajar peserta didik',
        'B' => 'Pemerataan pendidikan yang bermutu',
        'C' => 'Kompetensi dan kinerja pendidik dan tenaga kependidikan',
        'D' => 'Mutu dan relevansi pembelajaran',
        'E' => 'Pengelolaan sekolah yang partisipatif, transparan, dan akuntabel',
    ],

    // Akun super admin awal (pembuat akun). Diambil dari .env supaya
    // kredensialnya tidak tersimpan di repositori. Bila email atau kata
    // sandi kosong, seeder melewati pembuatan akun super admin.
    'superadmin' => [
        'nama' => env('SUPERADMIN_NAMA', 'Super Admin'),
        'email' => env('SUPERADMIN_EMAIL'),
        'kata_sandi' => env('SUPERADMIN_PASSWORD'),
    ],

];

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

    // Label yang dihitung sebagai "merah" dan "kuning" pada ringkasan profil.
    'label_merah' => ['Kurang'],
    'label_kuning' => ['Sedang'],
    'label_hijau' => ['Baik'],

    // Nilai yang menandai data tidak tersedia di berkas sumber.
    'nilai_kosong' => 'Tidak Tersedia',

    // Edisi berkas Rapor Pendidikan yang diimpor ke produksi. Edisi lama
    // hanya dipakai bila fitur tren (F6) dikerjakan.
    'edisi_produksi' => [2024, 2025],

];

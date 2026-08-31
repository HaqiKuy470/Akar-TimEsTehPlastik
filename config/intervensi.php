<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Pohon keputusan akar masalah
|--------------------------------------------------------------------------
|
| Untuk tiap indikator prioritas, AkarMasalahAnalyzer menelusuri daftar
| kandidat akar masalah, memeriksa label capaian indikator pendukung
| ('periksa'), lalu menetapkan tingkat keyakinan berdasarkan bukti yang
| terkumpul (lihat ARCHITECTURE.md bagian 6.3).
|
| Cakupan MVP: 16 indikator yang paling sering berlabel merah dan paling
| berdampak. Untuk indikator di luar daftar ini, sistem menampilkan capaian
| tanpa rekomendasi dan menyatakannya secara jujur. JANGAN menambah entri
| yang rantai sebabnya tidak tergambar dari definisi resmi Kemendikdasmen.
|
| Struktur tiap entri:
|   'nama'          Nama indikator, untuk tampilan.
|   'kandidat_akar' Daftar hipotesis akar masalah, diperiksa berurutan.
|       'kode'      Pengenal singkat kandidat.
|       'label'     Kalimat akar masalah yang dibaca pengguna.
|       'periksa'   Nomor indikator pendukung yang diperiksa labelnya.
|       'ambang'    Syarat bukti minimal agar kandidat dianggap berlaku.
|       'kegiatan'  Kode kegiatan di config/kegiatan.php.
|
| Kosakata 'ambang' yang dikenali AkarMasalahAnalyzer:
|   'minimal_satu_kurang'  minimal satu indikator 'periksa' berlabel Kurang.
|   'minimal_dua_kurang'   minimal dua indikator 'periksa' berlabel Kurang.
|   'mayoritas_bermasalah' lebih dari separuh indikator 'periksa' berlabel
|                          Kurang atau Sedang.
|
| Indikator 'periksa' berlabel 'Tidak Tersedia' tidak dihitung sebagai bukti
| dan tidak dihitung sebagai populasi ambang.
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Dimensi A - Hasil belajar (gejala yang paling sering ditanyakan)
    |--------------------------------------------------------------------------
    */

    'A.1' => [
        'nama' => 'Kemampuan literasi',
        'kandidat_akar' => [
            [
                'kode' => 'kualitas_pembelajaran',
                'label' => 'Kualitas praktik pembelajaran di kelas belum optimal',
                'periksa' => ['D.1', 'D.1.1', 'D.1.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['komunitas_belajar_literasi', 'pendampingan_pengawas_pembelajaran', 'pelatihan_mandiri_pmm'],
            ],
            [
                'kode' => 'budaya_refleksi',
                'label' => 'Budaya refleksi dan perbaikan pembelajaran oleh guru belum berjalan',
                'periksa' => ['D.2', 'D.2.2', 'D.2.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['lokakarya_refleksi_pembelajaran', 'program_guru_penggerak'],
            ],
            [
                'kode' => 'fasilitas_dan_gerakan_literasi',
                'label' => 'Dukungan lingkungan dan fasilitas literasi masih terbatas',
                'periksa' => ['D.3', 'D.3.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['gerakan_literasi_daerah', 'penyediaan_buku_bacaan_bermutu'],
            ],
        ],
    ],

    'A.2' => [
        'nama' => 'Kemampuan numerasi',
        'kandidat_akar' => [
            [
                'kode' => 'kualitas_pembelajaran',
                'label' => 'Kualitas praktik pembelajaran di kelas belum optimal',
                'periksa' => ['D.1', 'D.1.1', 'D.1.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['komunitas_belajar_numerasi', 'pendampingan_pengawas_pembelajaran', 'pelatihan_mandiri_pmm'],
            ],
            [
                'kode' => 'budaya_refleksi',
                'label' => 'Budaya refleksi dan perbaikan pembelajaran oleh guru belum berjalan',
                'periksa' => ['D.2', 'D.2.1', 'D.2.2'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['lokakarya_refleksi_pembelajaran', 'program_guru_penggerak'],
            ],
            [
                'kode' => 'kompetensi_guru',
                'label' => 'Kompetensi dan kesempatan pengembangan guru masih kurang',
                'periksa' => ['C.1', 'C.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['diklat_teknis_ptk', 'fasilitasi_sertifikasi_pendidik'],
            ],
        ],
    ],

    'A.3' => [
        'nama' => 'Karakter',
        'kandidat_akar' => [
            [
                'kode' => 'iklim_dan_pembelajaran_karakter',
                'label' => 'Penguatan karakter belum terintegrasi dalam pembelajaran dan iklim sekolah',
                'periksa' => ['D.1', 'D.8', 'D.8.1'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['penguatan_pendidikan_karakter', 'penguatan_toleransi_kebinekaan'],
            ],
            [
                'kode' => 'partisipasi_warga',
                'label' => 'Keterlibatan orang tua dan peserta didik dalam kehidupan sekolah masih rendah',
                'periksa' => ['E.1'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['penguatan_partisipasi_orang_tua'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dimensi B - Pemerataan
    |--------------------------------------------------------------------------
    */

    'B.1' => [
        'nama' => 'Kesenjangan literasi',
        'kandidat_akar' => [
            [
                'kode' => 'ketimpangan_mutu_pembelajaran',
                'label' => 'Mutu pembelajaran belum merata antarsekolah dan antarkelompok peserta didik',
                'periksa' => ['D.1', 'D.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['penurunan_kesenjangan_pembelajaran', 'pendampingan_pengawas_pembelajaran'],
            ],
            [
                'kode' => 'ketimpangan_sumber_daya_guru',
                'label' => 'Distribusi dan kecukupan guru belum merata',
                'periksa' => ['C.1', 'C.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['redistribusi_guru_daerah', 'pemenuhan_formasi_pppk_guru'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dimensi C - Ketersediaan dan kompetensi pendidik
    |--------------------------------------------------------------------------
    */

    'C.1' => [
        'nama' => 'Proporsi PTK bersertifikat',
        'kandidat_akar' => [
            [
                'kode' => 'akses_sertifikasi_terbatas',
                'label' => 'Akses guru terhadap jalur sertifikasi pendidik masih terbatas',
                'periksa' => ['C.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['fasilitasi_sertifikasi_pendidik', 'diklat_teknis_ptk'],
            ],
        ],
    ],

    'C.3' => [
        'nama' => 'Pengalaman pelatihan PTK',
        'kandidat_akar' => [
            [
                'kode' => 'program_pengembangan_belum_terjadwal',
                'label' => 'Program pengembangan kompetensi guru belum terjadwal dan merata',
                'periksa' => ['D.2', 'D.3', 'D.3.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['diklat_teknis_ptk', 'pelatihan_mandiri_pmm', 'program_guru_penggerak'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dimensi D - Mutu dan relevansi pembelajaran
    |--------------------------------------------------------------------------
    */

    'D.1' => [
        'nama' => 'Kualitas pembelajaran',
        'kandidat_akar' => [
            [
                'kode' => 'refleksi_lemah',
                'label' => 'Guru belum terbiasa merefleksikan dan memperbaiki praktik mengajar',
                'periksa' => ['D.2', 'D.2.2', 'D.2.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['lokakarya_refleksi_pembelajaran', 'komunitas_belajar_literasi', 'program_guru_penggerak'],
            ],
            [
                'kode' => 'kepemimpinan_instruksional_lemah',
                'label' => 'Kepala sekolah belum menjalankan kepemimpinan instruksional',
                'periksa' => ['D.3', 'D.3.2', 'D.3.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['pelatihan_kepala_sekolah_instruksional', 'pendampingan_pengawas_pembelajaran'],
            ],
            [
                'kode' => 'kompetensi_guru',
                'label' => 'Kompetensi guru belum ditopang pelatihan yang memadai',
                'periksa' => ['C.1', 'C.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['diklat_teknis_ptk', 'pelatihan_mandiri_pmm'],
            ],
        ],
    ],

    'D.2' => [
        'nama' => 'Refleksi dan perbaikan pembelajaran oleh guru',
        'kandidat_akar' => [
            [
                'kode' => 'dukungan_kepemimpinan_kurang',
                'label' => 'Kepala sekolah belum menciptakan ruang dan dukungan untuk refleksi guru',
                'periksa' => ['D.3', 'D.3.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['pelatihan_kepala_sekolah_instruksional', 'lokakarya_refleksi_pembelajaran'],
            ],
            [
                'kode' => 'kesempatan_belajar_guru_kurang',
                'label' => 'Guru kurang memperoleh kesempatan belajar tentang pembelajaran',
                'periksa' => ['C.3', 'D.2.1'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['komunitas_belajar_literasi', 'pelatihan_mandiri_pmm', 'program_guru_penggerak'],
            ],
        ],
    ],

    'D.3' => [
        'nama' => 'Kepemimpinan instruksional',
        'kandidat_akar' => [
            [
                'kode' => 'kapasitas_kepala_sekolah',
                'label' => 'Kapasitas kepala sekolah dalam memimpin pembelajaran belum terbangun',
                'periksa' => ['D.3.1', 'D.3.2', 'D.3.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['pelatihan_kepala_sekolah_instruksional', 'pendampingan_perencanaan_berbasis_data'],
            ],
            [
                'kode' => 'perencanaan_tidak_berbasis_data',
                'label' => 'Perencanaan program sekolah belum diturunkan dari data mutu',
                'periksa' => ['E.2', 'E.5'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['pendampingan_perencanaan_berbasis_data', 'optimalisasi_bos_untuk_mutu'],
            ],
        ],
    ],

    'D.4' => [
        'nama' => 'Iklim keamanan satuan pendidikan',
        'kandidat_akar' => [
            [
                'kode' => 'kebijakan_pencegahan_kekerasan_belum_ada',
                'label' => 'Satuan pendidikan belum memiliki program dan kebijakan pencegahan kekerasan',
                'periksa' => ['E.5'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['penyusunan_kebijakan_anti_kekerasan', 'penguatan_tppk'],
            ],
            [
                'kode' => 'pemahaman_warga_sekolah_rendah',
                'label' => 'Pemahaman dan sikap warga sekolah terhadap kekerasan masih rendah',
                'periksa' => ['D.4.3', 'D.4.4'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['sosialisasi_pencegahan_kekerasan', 'penguatan_tppk'],
            ],
        ],
    ],

    'D.6' => [
        'nama' => 'Iklim kesetaraan gender',
        'kandidat_akar' => [
            [
                'kode' => 'kebijakan_dan_edukasi_gender_kurang',
                'label' => 'Edukasi dan kebijakan kesetaraan gender belum berjalan di sekolah',
                'periksa' => ['E.5', 'D.4'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['sosialisasi_kesetaraan_gender', 'penyusunan_kebijakan_anti_kekerasan'],
            ],
        ],
    ],

    'D.8' => [
        'nama' => 'Iklim kebinekaan',
        'kandidat_akar' => [
            [
                'kode' => 'praktik_pembelajaran_kebinekaan_lemah',
                'label' => 'Pembelajaran belum secara sengaja menumbuhkan toleransi dan komitmen kebangsaan',
                'periksa' => ['D.1', 'D.8.1'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['penguatan_toleransi_kebinekaan', 'penguatan_pendidikan_karakter'],
            ],
            [
                'kode' => 'kebijakan_intoleransi_belum_ada',
                'label' => 'Satuan pendidikan belum memiliki kebijakan pencegahan intoleransi',
                'periksa' => ['E.5'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['penyusunan_kebijakan_anti_kekerasan'],
            ],
        ],
    ],

    'D.10' => [
        'nama' => 'Iklim inklusivitas',
        'kandidat_akar' => [
            [
                'kode' => 'layanan_kebutuhan_khusus_terbatas',
                'label' => 'Kapasitas guru dan layanan bagi peserta didik berkebutuhan khusus masih terbatas',
                'periksa' => ['D.10.1', 'C.3'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['program_sekolah_ramah_inklusi', 'diklat_teknis_ptk'],
            ],
            [
                'kode' => 'kebijakan_inklusi_belum_ada',
                'label' => 'Satuan pendidikan belum memiliki kebijakan dan program layanan inklusif',
                'periksa' => ['E.5'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['penyusunan_kebijakan_anti_kekerasan', 'program_sekolah_ramah_inklusi'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dimensi E - Pengelolaan pendidikan
    |--------------------------------------------------------------------------
    */

    'E.1' => [
        'nama' => 'Partisipasi warga satuan pendidikan',
        'kandidat_akar' => [
            [
                'kode' => 'wadah_partisipasi_tidak_berfungsi',
                'label' => 'Komite sekolah dan forum warga belum berfungsi sebagai wadah perencanaan bersama',
                'periksa' => ['D.3', 'E.5'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['penguatan_partisipasi_orang_tua', 'pendampingan_perencanaan_berbasis_data'],
            ],
        ],
    ],

    'E.2' => [
        'nama' => 'Proporsi pemanfaatan sumber daya sekolah untuk peningkatan mutu',
        'kandidat_akar' => [
            [
                'kode' => 'perencanaan_anggaran_lemah',
                'label' => 'Perencanaan dan pembelanjaan anggaran sekolah belum diarahkan ke peningkatan mutu',
                'periksa' => ['D.3', 'E.5'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['optimalisasi_bos_untuk_mutu', 'pendampingan_perencanaan_berbasis_data', 'digitalisasi_pengelolaan_anggaran'],
            ],
        ],
    ],

    'E.5' => [
        'nama' => 'Program dan kebijakan satuan pendidikan',
        'kandidat_akar' => [
            [
                'kode' => 'kapasitas_penyusunan_kebijakan_kurang',
                'label' => 'Sekolah belum memiliki kapasitas menyusun program dan kebijakan wajib',
                'periksa' => ['D.3', 'D.3.1', 'D.4'],
                'ambang' => 'minimal_satu_kurang',
                'kegiatan' => ['penyusunan_kebijakan_anti_kekerasan', 'penguatan_tppk', 'pendampingan_perencanaan_berbasis_data'],
            ],
        ],
    ],

];

<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Katalog kegiatan rencana tindak lanjut
|--------------------------------------------------------------------------
|
| Setiap kode kegiatan di config/intervensi.php merujuk ke satu entri di sini.
| Isinya disusun mengacu pada kerangka Perencanaan Berbasis Data (identifikasi -
| refleksi - benahi) dan menu kegiatan ARKAS/BOS. Nilai-nilai ini adalah draf
| awal yang akan disunting pengguna sebelum diekspor, bukan angka final.
|
| Struktur tiap entri:
|   'nama'                   Judul kegiatan yang muncul di dokumen rencana.
|   'deskripsi'              Penjelasan singkat bentuk kegiatan.
|   'penanggung_jawab'       Unit tipikal yang menjalankan (dapat diubah pengguna).
|   'indikator_keberhasilan' Ukuran keberhasilan yang bisa dipantau.
|   'perkiraan_waktu'        Rentang waktu tipikal dalam satu tahun anggaran.
|
| Aturan bisnis, bukan kode. Bila katalog kegiatan berubah, ubah berkas ini.
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Pembelajaran literasi dan numerasi
    |--------------------------------------------------------------------------
    */

    'komunitas_belajar_literasi' => [
        'nama' => 'Pendampingan komunitas belajar antarsekolah untuk literasi',
        'deskripsi' => 'Membentuk dan mendampingi komunitas belajar guru lintas satuan pendidikan yang berfokus pada praktik pembelajaran literasi: pemahaman teks informasi dan sastra, strategi membaca, serta asesmen formatif literasi.',
        'penanggung_jawab' => 'Bidang Pembinaan sesuai jenjang',
        'indikator_keberhasilan' => 'Minimal 80% sekolah prioritas memiliki komunitas belajar aktif yang bertemu rutin, dan indikator D.1 atau D.2 naik satu tingkat pada Asesmen Nasional berikutnya.',
        'perkiraan_waktu' => 'Triwulan I sampai IV',
    ],

    'komunitas_belajar_numerasi' => [
        'nama' => 'Pendampingan komunitas belajar antarsekolah untuk numerasi',
        'deskripsi' => 'Komunitas belajar guru yang berfokus pada pembelajaran numerasi kontekstual: domain bilangan, aljabar, geometri, serta penalaran matematis, dengan berbagi praktik dan penyusunan bahan ajar bersama.',
        'penanggung_jawab' => 'Bidang Pembinaan sesuai jenjang',
        'indikator_keberhasilan' => 'Proporsi peserta didik mencapai kompetensi minimum numerasi (A.2) naik dari label Kurang ke Sedang di sebagian besar sekolah prioritas.',
        'perkiraan_waktu' => 'Triwulan I sampai IV',
    ],

    'pendampingan_pengawas_pembelajaran' => [
        'nama' => 'Pendampingan terjadwal oleh pengawas dan penilik',
        'deskripsi' => 'Menetapkan jadwal kunjungan pendampingan pengawas ke sekolah prioritas dengan fokus observasi kelas, umpan balik pembelajaran, dan pendampingan penyusunan rencana perbaikan.',
        'penanggung_jawab' => 'Koordinator Pengawas',
        'indikator_keberhasilan' => 'Setiap sekolah prioritas menerima minimal empat kunjungan pendampingan bermutu per tahun dengan catatan tindak lanjut yang diverifikasi.',
        'perkiraan_waktu' => 'Triwulan I sampai IV',
    ],

    'pelatihan_mandiri_pmm' => [
        'nama' => 'Pemanfaatan Pelatihan Mandiri di Platform Merdeka Mengajar',
        'deskripsi' => 'Mendorong dan memfasilitasi guru menyelesaikan topik Pelatihan Mandiri yang relevan (literasi, numerasi, asesmen, kurikulum) disertai pendampingan penerapan di kelas oleh komunitas belajar.',
        'penanggung_jawab' => 'Bidang GTK',
        'indikator_keberhasilan' => 'Minimal 70% guru di sekolah prioritas menyelesaikan topik pelatihan yang ditetapkan dan menerapkannya, dibuktikan aksi nyata.',
        'perkiraan_waktu' => 'Triwulan I sampai III',
    ],

    'gerakan_literasi_daerah' => [
        'nama' => 'Penguatan gerakan literasi tingkat daerah',
        'deskripsi' => 'Program terpadu penyediaan waktu membaca terjadwal, pojok baca kelas, dan kegiatan literasi keluarga, dikoordinasikan lintas sekolah dengan dukungan perpustakaan daerah.',
        'penanggung_jawab' => 'Bidang Pembinaan sesuai jenjang bersama Dinas Perpustakaan',
        'indikator_keberhasilan' => 'Seluruh sekolah prioritas menjalankan kegiatan membaca terjadwal, dan kompetensi membaca teks informasi (A.1.1) membaik.',
        'perkiraan_waktu' => 'Triwulan II sampai IV',
    ],

    'penyediaan_buku_bacaan_bermutu' => [
        'nama' => 'Penyediaan dan pemanfaatan buku bacaan bermutu',
        'deskripsi' => 'Pengadaan buku bacaan berjenjang sesuai daftar rekomendasi Kemendikdasmen untuk sekolah dengan fasilitas literasi rendah, disertai pelatihan pemanfaatannya dalam pembelajaran.',
        'penanggung_jawab' => 'Bidang Sarana dan Prasarana',
        'indikator_keberhasilan' => 'Kesenjangan fasilitas literasi (D.14) menurun dan rasio buku bacaan per peserta didik memenuhi standar minimal di sekolah prioritas.',
        'perkiraan_waktu' => 'Triwulan I sampai II',
    ],

    /*
    |--------------------------------------------------------------------------
    | Refleksi, perbaikan, dan kepemimpinan pembelajaran
    |--------------------------------------------------------------------------
    */

    'lokakarya_refleksi_pembelajaran' => [
        'nama' => 'Lokakarya budaya refleksi dan perbaikan pembelajaran',
        'deskripsi' => 'Serangkaian lokakarya untuk kepala sekolah dan guru inti mengenai siklus refleksi terstruktur: analisis data belajar, perumusan masalah, uji praktik baru, dan berbagi hasil.',
        'penanggung_jawab' => 'Bidang GTK',
        'indikator_keberhasilan' => 'Indikator refleksi dan perbaikan pembelajaran oleh guru (D.2) naik satu tingkat, dan setiap sekolah prioritas memiliki agenda refleksi rutin terdokumentasi.',
        'perkiraan_waktu' => 'Triwulan I sampai III',
    ],

    'pelatihan_kepala_sekolah_instruksional' => [
        'nama' => 'Pelatihan kepemimpinan instruksional kepala sekolah',
        'deskripsi' => 'Peningkatan kapasitas kepala sekolah dalam menerjemahkan visi-misi menjadi program pembelajaran, mengelola kurikulum, melakukan supervisi akademik, dan mendukung refleksi guru.',
        'penanggung_jawab' => 'Bidang GTK',
        'indikator_keberhasilan' => 'Kepemimpinan instruksional (D.3) membaik, dibuktikan rencana kerja sekolah yang berorientasi hasil belajar dan jadwal supervisi akademik yang berjalan.',
        'perkiraan_waktu' => 'Triwulan I sampai II',
    ],

    'pendampingan_perencanaan_berbasis_data' => [
        'nama' => 'Pendampingan penyusunan rencana berbasis data',
        'deskripsi' => 'Bimbingan teknis dan pendampingan sekolah dalam membaca Rapor Pendidikan, memilih dan merefleksikan akar masalah, serta menyusun rencana kegiatan dan anggaran yang menjawab masalah tersebut.',
        'penanggung_jawab' => 'Bidang Perencanaan',
        'indikator_keberhasilan' => 'Seluruh sekolah prioritas menyusun dokumen perencanaan yang benar-benar diturunkan dari akar masalah Rapor Pendidikan, bukan salinan tahun sebelumnya.',
        'perkiraan_waktu' => 'Triwulan I',
    ],

    'program_guru_penggerak' => [
        'nama' => 'Optimalisasi peran Guru Penggerak dan sekolah penggerak',
        'deskripsi' => 'Menempatkan dan memberdayakan Guru Penggerak sebagai fasilitator komunitas belajar di sekolah prioritas, serta menjadikan sekolah penggerak sebagai tempat magang praktik baik.',
        'penanggung_jawab' => 'Bidang GTK',
        'indikator_keberhasilan' => 'Setiap sekolah prioritas terhubung dengan minimal satu Guru Penggerak aktif sebagai pendamping komunitas belajar.',
        'perkiraan_waktu' => 'Triwulan I sampai IV',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ketersediaan dan kompetensi pendidik
    |--------------------------------------------------------------------------
    */

    'fasilitasi_sertifikasi_pendidik' => [
        'nama' => 'Fasilitasi sertifikasi pendidik bagi guru yang belum bersertifikat',
        'deskripsi' => 'Pendataan guru yang memenuhi syarat, sosialisasi jalur Pendidikan Profesi Guru, dan dukungan administratif serta pembelajaran persiapan bagi guru sekolah prioritas.',
        'penanggung_jawab' => 'Bidang GTK',
        'indikator_keberhasilan' => 'Proporsi PTK bersertifikat (C.1) naik, dengan target penambahan jumlah guru bersertifikat yang disepakati bersama.',
        'perkiraan_waktu' => 'Triwulan I sampai IV',
    ],

    'diklat_teknis_ptk' => [
        'nama' => 'Diklat teknis dan pelatihan berkelanjutan bagi PTK',
        'deskripsi' => 'Penyelenggaraan pelatihan teknis terjadwal untuk guru dan tenaga kependidikan sesuai kebutuhan hasil analisis, dikombinasikan dengan penerapan terbimbing di sekolah.',
        'penanggung_jawab' => 'Bidang GTK',
        'indikator_keberhasilan' => 'Pengalaman pelatihan PTK (C.3) meningkat dari label Kurang, dan cakupan guru yang mengikuti pelatihan relevan naik.',
        'perkiraan_waktu' => 'Triwulan I sampai III',
    ],

    'redistribusi_guru_daerah' => [
        'nama' => 'Penataan dan pemerataan distribusi guru antarsekolah',
        'deskripsi' => 'Analisis kebutuhan dan kelebihan guru per sekolah dan mata pelajaran, lalu penataan penempatan agar sekolah kekurangan guru terpenuhi, sesuai ketentuan yang berlaku.',
        'penanggung_jawab' => 'Bidang GTK bersama BKD',
        'indikator_keberhasilan' => 'Indeks Distribusi Guru (C.7) membaik dan tidak ada sekolah prioritas yang kekurangan guru kelas atau guru mata pelajaran inti.',
        'perkiraan_waktu' => 'Triwulan I sampai II',
    ],

    'pemenuhan_formasi_pppk_guru' => [
        'nama' => 'Pengusulan dan pemenuhan formasi guru ASN PPPK',
        'deskripsi' => 'Penyusunan usulan formasi guru ASN berbasis kebutuhan riil sekolah dan percepatan penempatan guru PPPK yang telah lulus ke sekolah prioritas.',
        'penanggung_jawab' => 'Bidang GTK bersama BKD',
        'indikator_keberhasilan' => 'Kecukupan formasi guru ASN (C.8) meningkat sesuai kebutuhan peningkatan indeks distribusi guru.',
        'perkiraan_waktu' => 'Triwulan I sampai IV',
    ],

    /*
    |--------------------------------------------------------------------------
    | Iklim keamanan, kebinekaan, kesetaraan, dan inklusivitas
    |--------------------------------------------------------------------------
    */

    'penguatan_tppk' => [
        'nama' => 'Pembentukan dan penguatan Tim Pencegahan dan Penanganan Kekerasan',
        'deskripsi' => 'Memastikan setiap satuan pendidikan membentuk TPPK dan daerah membentuk Satuan Tugas, disertai pelatihan penanganan kasus, alur pelaporan, dan pendampingan korban.',
        'penanggung_jawab' => 'Bidang Pembinaan sesuai jenjang bersama Satgas PPK Daerah',
        'indikator_keberhasilan' => 'Seluruh sekolah prioritas memiliki TPPK aktif dengan mekanisme pelaporan yang diketahui warga sekolah, dan indikator iklim keamanan (D.4) membaik.',
        'perkiraan_waktu' => 'Triwulan I sampai II',
    ],

    'sosialisasi_pencegahan_kekerasan' => [
        'nama' => 'Sosialisasi dan edukasi pencegahan kekerasan di lingkungan pendidikan',
        'deskripsi' => 'Kegiatan edukasi berkala bagi peserta didik, guru, dan orang tua mengenai perundungan, hukuman fisik, kekerasan seksual, serta rokok, minuman keras, dan narkoba.',
        'penanggung_jawab' => 'Bidang Pembinaan sesuai jenjang',
        'indikator_keberhasilan' => 'Pemahaman dan sikap terhadap perundungan (D.4.3) meningkat dan laporan pengalaman perundungan (D.4.4) menurun.',
        'perkiraan_waktu' => 'Triwulan II sampai IV',
    ],

    'penyusunan_kebijakan_anti_kekerasan' => [
        'nama' => 'Penyusunan program dan kebijakan satuan pendidikan yang wajib ada',
        'deskripsi' => 'Pendampingan sekolah menyusun dan menerapkan kebijakan pencegahan dan penanganan perundungan, hukuman fisik, kekerasan seksual, penyalahgunaan narkoba, intoleransi, dan ketidaksetaraan gender.',
        'penanggung_jawab' => 'Bidang Pembinaan sesuai jenjang',
        'indikator_keberhasilan' => 'Program dan kebijakan satuan pendidikan (E.5) mencapai label Baik di seluruh sekolah prioritas, dengan dokumen kebijakan yang disosialisasikan.',
        'perkiraan_waktu' => 'Triwulan I sampai II',
    ],

    'penguatan_pendidikan_karakter' => [
        'nama' => 'Penguatan pendidikan karakter dan Projek Penguatan Profil Pelajar Pancasila',
        'deskripsi' => 'Pendampingan perencanaan dan pelaksanaan projek penguatan profil pelajar Pancasila yang kontekstual, serta pengintegrasian nilai karakter dalam pembelajaran sehari-hari.',
        'penanggung_jawab' => 'Bidang Pembinaan sesuai jenjang',
        'indikator_keberhasilan' => 'Indikator karakter (A.3) naik satu tingkat, dan setiap sekolah prioritas melaksanakan minimal dua projek penguatan profil per tahun dengan refleksi.',
        'perkiraan_waktu' => 'Triwulan I sampai IV',
    ],

    'program_sekolah_ramah_inklusi' => [
        'nama' => 'Penguatan layanan pendidikan inklusif',
        'deskripsi' => 'Pelatihan guru mengenai identifikasi dan layanan peserta didik berkebutuhan khusus dan berbakat istimewa, penyediaan guru pembimbing khusus, serta penyesuaian sarana dasar.',
        'penanggung_jawab' => 'Bidang Pembinaan sesuai jenjang',
        'indikator_keberhasilan' => 'Iklim inklusivitas (D.10) membaik dan layanan disabilitas (D.10.1) tersedia di seluruh sekolah prioritas yang menerima peserta didik berkebutuhan khusus.',
        'perkiraan_waktu' => 'Triwulan II sampai IV',
    ],

    'sosialisasi_kesetaraan_gender' => [
        'nama' => 'Penguatan iklim kesetaraan gender',
        'deskripsi' => 'Edukasi warga sekolah mengenai kesetaraan gender, peninjauan bahan ajar dan kebijakan agar bebas bias gender, serta pendampingan sikap dan perilaku setara di lingkungan sekolah.',
        'penanggung_jawab' => 'Bidang Pembinaan sesuai jenjang',
        'indikator_keberhasilan' => 'Iklim kesetaraan gender (D.6) membaik, dibuktikan penurunan laporan perlakuan tidak setara berbasis gender.',
        'perkiraan_waktu' => 'Triwulan II sampai IV',
    ],

    'penguatan_toleransi_kebinekaan' => [
        'nama' => 'Penguatan iklim kebinekaan dan toleransi',
        'deskripsi' => 'Kegiatan lintas budaya dan agama, penguatan komitmen kebangsaan, serta pelatihan guru memfasilitasi dialog toleransi di kelas.',
        'penanggung_jawab' => 'Bidang Pembinaan sesuai jenjang',
        'indikator_keberhasilan' => 'Iklim kebinekaan (D.8) dan toleransi agama dan budaya (D.8.1) membaik pada Asesmen Nasional berikutnya.',
        'perkiraan_waktu' => 'Triwulan II sampai IV',
    ],

    /*
    |--------------------------------------------------------------------------
    | Partisipasi warga dan pengelolaan sumber daya
    |--------------------------------------------------------------------------
    */

    'penguatan_partisipasi_orang_tua' => [
        'nama' => 'Penguatan partisipasi orang tua dan peserta didik',
        'deskripsi' => 'Revitalisasi komite sekolah, kelas orang tua, dan forum peserta didik agar terlibat dalam perencanaan program dan pemantauan pembelajaran, bukan sekadar urusan iuran.',
        'penanggung_jawab' => 'Bidang Pembinaan sesuai jenjang',
        'indikator_keberhasilan' => 'Partisipasi warga satuan pendidikan (E.1) membaik, dengan bukti keterlibatan orang tua dan peserta didik dalam penyusunan program sekolah.',
        'perkiraan_waktu' => 'Triwulan I sampai IV',
    ],

    'optimalisasi_bos_untuk_mutu' => [
        'nama' => 'Optimalisasi pemanfaatan dana untuk peningkatan mutu pembelajaran',
        'deskripsi' => 'Pendampingan sekolah mengarahkan porsi belanja BOS dan sumber daya lain ke kegiatan yang berdampak langsung pada pembelajaran, sesuai prioritas akar masalah.',
        'penanggung_jawab' => 'Bidang Perencanaan bersama Tim BOS',
        'indikator_keberhasilan' => 'Proporsi pemanfaatan sumber daya sekolah untuk peningkatan mutu (E.2) naik dari label Kurang.',
        'perkiraan_waktu' => 'Triwulan I sampai IV',
    ],

    'digitalisasi_pengelolaan_anggaran' => [
        'nama' => 'Digitalisasi pengelolaan dan pembelanjaan anggaran sekolah',
        'deskripsi' => 'Bimbingan teknis penggunaan sistem pengelolaan anggaran daring dan Sistem Informasi Pengadaan Sekolah, disertai pendampingan pertanggungjawaban.',
        'penanggung_jawab' => 'Bidang Perencanaan bersama Tim BOS',
        'indikator_keberhasilan' => 'Pemanfaatan TIK untuk pengelolaan anggaran (E.3) membaik dan proporsi pembelanjaan daring meningkat.',
        'perkiraan_waktu' => 'Triwulan I sampai III',
    ],

    'penurunan_kesenjangan_pembelajaran' => [
        'nama' => 'Program afirmasi untuk menutup kesenjangan capaian',
        'deskripsi' => 'Pemetaan kelompok peserta didik dan sekolah yang tertinggal berdasarkan gender, status sosial ekonomi, dan wilayah, lalu pemberian dukungan tambahan yang terarah.',
        'penanggung_jawab' => 'Bidang Perencanaan bersama Bidang Pembinaan',
        'indikator_keberhasilan' => 'Kesenjangan literasi (B.1) menurun, dengan selisih capaian antar kelompok yang mengecil pada Asesmen Nasional berikutnya.',
        'perkiraan_waktu' => 'Triwulan I sampai IV',
    ],

];

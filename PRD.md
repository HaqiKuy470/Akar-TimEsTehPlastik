# PRD — AKAR
**Analisis Kausal dan Rekomendasi**
Sistem penerjemah Rapor Pendidikan menjadi rencana tindak lanjut bagi Dinas Pendidikan

| | |
|---|---|
| Versi | 1.0 |
| Tanggal | 30 Agustus 2026 |
| Kompetisi | HoloDev — HOLOGY 9.0 Universitas Brawijaya |
| Subtema | Pendidikan |
| Deadline submission | 7 September 2026, 23.59 WIB |
| Status | Draf untuk dikunci hari ini |

---

## 1. Ringkasan

AKAR membaca berkas Rapor Pendidikan yang diterbitkan Kemendikdasmen, menemukan indikator yang berlabel merah, menelusuri akar masalahnya, lalu menghasilkan draf rencana tindak lanjut yang bisa langsung dibawa ke rapat perencanaan.

Sistem bekerja pada dua level dengan logika analisis yang sama:

| Level | Pengguna | Sumber berkas | Status di MVP |
|---|---|---|---|
| **Kabupaten/kota** | Dinas Pendidikan | Rapor Pendidikan Indonesia, terbuka untuk publik tanpa akun | Jalur utama, didemokan penuh |
| **Satuan pendidikan** | Kepala sekolah dan tim | Rapor Pendidikan sekolah, diunduh sendiri lewat akun belajar.id | Mode kedua, alur unggah tersedia |

Yang membedakan hanya berkas masukannya. Parser, mesin analisis, dan generator rencana aksi identik untuk keduanya.

Satu kalimat untuk pitching:

> Rapor Pendidikan memberi tahu bahwa nilainya merah. AKAR memberi tahu apa yang harus dilakukan Senin pagi. Satu logika analisis, dua level pengguna: dinas melihat seluruh kabupatennya, kepala sekolah melihat sekolahnya sendiri.

---

## 2. Masalah

### 2.1 Pernyataan masalah

Pemerintah sudah menyediakan data mutu pendidikan yang lengkap dan terbuka. Rapor Pendidikan Indonesia memuat 274 indikator untuk 38 provinsi dan seluruh kabupaten/kota, dan siapa pun bisa mengunduhnya tanpa akun. Tetapi data itu berhenti sebagai diagnosis.

Seorang kepala bidang di Dinas Pendidikan yang mengunduh berkas tersebut akan menemukan spreadsheet 38 sheet, masing-masing sekitar 951 baris dan 548 kolom, dengan header bertingkat tiga baris. Untuk mengetahui indikator mana yang bermasalah di daerahnya, ia harus menelusuri manual. Untuk tahu daerahnya ada di posisi berapa dibanding tetangga, ia harus membuka berkas lain. Untuk menyusun rencana pembenahan, ia harus menebak sendiri kegiatan apa yang cocok.

Akibatnya Perencanaan Berbasis Data sering berhenti sebagai dokumen formalitas yang disalin dari tahun sebelumnya.

### 2.2 Bukti pendukung

| Fakta | Sumber |
|---|---|
| 2,92 juta anak Indonesia tidak mengenyam pendidikan formal, mayoritas usia 16–18 tahun | Sekjen Kemendikdasmen, Juli 2026 |
| Asesmen Nasional 2026 dilaksanakan di 497.138 satuan pendidikan | Program Prioritas Kemendikdasmen TA 2026 |
| Akreditasi menyasar 99.235 satuan pendidikan | Program Prioritas Kemendikdasmen TA 2026 |
| Anggaran Kemendikdasmen 2026 sebesar Rp52,12 triliun | Kemendikdasmen, Januari 2026 |
| Hanya sekitar 34% siswa SMP mencapai kompetensi minimum literasi | Asesmen Nasional 2023 |
| Rapor Pendidikan diperbarui 12 Maret 2026 dengan tiga indikator mutu baru | BSKAP Kemendikdasmen |

Data mengalir deras. Kapasitas menerjemahkannya menjadi tindakan tidak tumbuh secepat itu.

### 2.3 Kenapa belum ada yang menyelesaikan

Platform Rapor Pendidikan resmi memang memberikan rekomendasi pembenahan, tetapi hanya untuk pengguna yang login dengan akun belajar.id di level satuan pendidikan atau dinas. Untuk level daerah, yang tersedia bagi publik adalah berkas mentah tanpa alat bantu analisis. Tidak ada fitur perbandingan antardaerah, tidak ada analisis tren lintas tahun, dan tidak ada generator dokumen perencanaan.

---

## 3. Pengguna

Produk melayani dua level. Keduanya menghadapi masalah yang identik: menerima diagnosis, tidak menerima arah tindakan.

### 3.1 Level kabupaten/kota — jalur utama MVP

**Bu Retno, Kepala Bidang Pembinaan SMP, Dinas Pendidikan Kabupaten**
Menguasai substansi pendidikan, tidak menguasai Excel tingkat lanjut. Setiap awal tahun anggaran harus menyusun program prioritas untuk puluhan sekolah. Waktunya habis untuk rapat. Butuh bahan yang bisa langsung dibawa ke forum, bukan spreadsheet yang harus diolah dulu.

**Pak Adi, Analis Perencanaan Dinas**
Yang benar-benar membuka berkas Excel. Butuh alat yang mempercepat pekerjaannya, dan butuh angka yang bisa dipertanggungjawabkan saat ditanya atasan.

### 3.2 Level satuan pendidikan — mode kedua

**Bu Sri, Kepala SMP Negeri**
Punya akun belajar.id, bisa mengunduh Rapor Pendidikan sekolahnya sendiri. Setiap tahun wajib menyusun Rencana Kerja Tahunan berbasis data. Membuka berkasnya, melihat literasi berlabel Kurang, lalu tidak tahu harus mengubah apa. Akhirnya menyalin RKT tahun lalu.

**Pak Bagus, Wakil Kepala Bidang Kurikulum**
Yang ditugasi menyusun dokumennya. Butuh usulan kegiatan konkret yang bisa dia diskusikan dengan guru, bukan istilah perencanaan yang abstrak.

Untuk level ini, pengguna mengunggah berkas Rapor Pendidikan sekolahnya sendiri. Sistem tidak menyimpan atau mengakses data sekolah mana pun tanpa berkas yang diunggah pengguna secara sadar.

### 3.3 Bukan pengguna

Siswa, orang tua, dan guru mata pelajaran secara individual. Membuka produk untuk mereka akan melebarkan ruang lingkup dan melemahkan fokus.

### 3.4 Kenapa dinas menjadi jalur demo

Bukan karena sekolah kurang penting, melainkan karena ketersediaan data. Rapor Pendidikan level kabupaten/kota terbuka untuk siapa pun tanpa akun, sehingga penilai dapat memverifikasi sendiri sumbernya saat sesi tanya jawab. Rapor Pendidikan level satuan pendidikan hanya dapat diunduh pemilik akun belajar.id sekolah tersebut, sehingga tidak dapat dipakai sebagai bahan demo publik.

Konsekuensinya, demo dilakukan dengan data kabupaten, dan mode sekolah ditampilkan sebagai alur unggah yang sudah tersedia di aplikasi.

---

## 4. Ruang Lingkup

### 4.1 Termasuk (MVP)

1. Impor dan parsing berkas Rapor Pendidikan Indonesia level daerah
2. Profil capaian per kabupaten/kota per jenjang
3. Deteksi indikator berlabel merah dan kuning
4. Analisis akar masalah berbasis pohon keputusan
5. Perbandingan antardaerah dalam satu provinsi
6. Analisis tren lintas tahun (2022–2025)
7. Generator draf rencana tindak lanjut
8. Ekspor PDF dan Excel
9. Alur unggah berkas Rapor Pendidikan level satuan pendidikan (mode sekolah)

Butir 9 memakai seluruh komponen yang sudah dibangun untuk butir 1 sampai 8. Biaya pengembangan tambahannya terbatas pada penyesuaian parser dan satu halaman unggah.

### 4.2 Tidak termasuk

- Integrasi langsung ke Dapodik atau sistem informasi dinas
- Manajemen anggaran atau pengadaan
- Akun multi-tenant untuk banyak dinas atau sekolah sekaligus
- Aplikasi mobile
- Analisis data individual siswa atau guru

Seluruhnya masuk peta jalan pascakompetisi, bukan MVP.

### 4.3 Catatan penting soal mode sekolah

Struktur berkas Rapor Pendidikan level satuan pendidikan **belum diverifikasi tim**. Dugaan kuat strukturnya menyerupai versi daerah karena berasal dari sistem yang sama, tetapi ini belum dibuktikan.

Dua skenario:

| Kondisi | Tindakan |
|---|---|
| Tim memperoleh berkas contoh sebelum 2 September | Bangun parser variannya, jadikan mode sekolah fitur nyata yang bisa didemokan |
| Tim tidak memperoleh berkas contoh | Tetap sediakan halaman unggah dengan validasi struktur, dan nyatakan terus terang di proposal bahwa mode ini belum diuji dengan berkas asli |

Jangan menjanjikan mode sekolah berfungsi penuh jika belum pernah diuji. Klaim yang tidak terbukti akan lebih merugikan di sesi tanya jawab dibanding pengakuan jujur soal batas pengujian.

---

## 5. Sumber Data

Seluruh data berasal dari Portal Satu Data Kemendikdasmen di `data.kemendikdasmen.go.id`, diakses 30 Agustus 2026. Tidak ada data pribadi siswa atau guru yang diproses.

### 5.1 Berkas utama

**Data Rapor Pendidikan Indonesia** — 4 edisi (data tahun 2022, 2023, 2024, 2025), masing-masing 16–21 MB.

Struktur setiap berkas:

| Sheet | Isi |
|---|---|
| `Metadata` | 274 indikator dengan definisi lengkap |
| `Nasional` | Agregat tingkat nasional |
| 38 sheet provinsi | Aceh sampai Sumatera Utara |

Struktur sheet provinsi (Jawa Timur: 951 baris × 548 kolom):

| Baris | Isi |
|---|---|
| 1–5 | Judul dan catatan |
| 6 | Kode indikator induk (`A.1`, `A.2`, dst.) |
| 7 | Nama indikator (`A.1 Kemampuan literasi`, `A.1.1 Kompetensi membaca teks informasi`) |
| 8 | Nama kolom (`Provinsi`, `Kabupaten/Kota`, `Jenis Satuan Pendidikan`, `Status Satuan Pendidikan`, lalu berulang `Label Capaian 2025` dan `Perubahan Nilai Capaian`) |
| 9 dan seterusnya | Data |

Kolom A sampai D adalah dimensi. Kolom E dan seterusnya berpasangan dua kolom per indikator.

Nilai yang mungkin muncul:

- **Label Capaian**: `Baik`, `Sedang`, `Kurang`, `Tidak Tersedia`
- **Perubahan Nilai Capaian**: `Naik`, `Turun`, `Tidak berubah`, `Tidak Tersedia`
- **Jenis Satuan Pendidikan**: SD Umum, MI, SMP Umum, MTs, SMA Umum, MA, SMK Umum, SLB, SMALB, SMPLB, Pendidikan Keagamaan, Kesetaraan, dan turunannya
- **Status**: Negeri, Swasta, Semua (Negeri dan Swasta)

Baris dengan `Kabupaten/Kota` bernilai `-` adalah agregat provinsi. Ini penting untuk pembanding.

### 5.2 Berkas metadata indikator

Sheet `Metadata` sudah diekstrak ke `METADATA_INDIKATOR_RAPOR_PENDIDIKAN.csv` dengan 274 baris dan kolom berikut:

`Jenis Layanan/Jenjang Pendidikan`, `Nomor Indikator`, `Nama Indikator`, `Definisi Konseptual`, `Definisi Operasional Daerah`, `Sumber Data`, `Label Merah`, `Definisi Label Merah`, `Label Kuning`, `Definisi Label Kuning`, `Label Hijau`, `Definisi Label Hijau`, `Ketersediaan Indikator di Tingkat Satuan Pendidikan`, `Ketersediaan Indikator di Tingkat Kabupaten/Kota`, `Ketersediaan Indikator di Tingkat Provinsi`

Ini aset paling berharga dalam proyek ini. Ambang batas merah, kuning, dan hijau sudah didefinisikan resmi oleh Kemendikdasmen. Sistem tidak mengarang kriteria sendiri, hanya membacanya dari sumber resmi. Argumen ini wajib disampaikan saat sesi tanya jawab.

Tiga kolom terakhir menentukan indikator mana yang valid dipakai di level kabupaten/kota. Indikator yang tidak tersedia di level itu harus difilter keluar.

### 5.3 Berkas pendukung

288 berkas indikator prioritas tahun 2021–2025, termasuk Jumlah Anak Tidak Sekolah yang Mendapatkan Layanan Pendidikan, Indeks Pemerataan Guru, dan Persentase Satuan Pendidikan yang Memenuhi SNP. Dipakai sebagai pengaya konteks, bukan sumber utama.

---

## 6. Kebutuhan Fungsional

Setiap fitur ditulis dengan kriteria penerimaan yang bisa diuji. Prioritas P0 wajib selesai, P1 dikerjakan bila waktu memungkinkan.

---

### F1 — Impor dan Parsing Berkas · P0

Sistem membaca berkas Rapor Pendidikan Indonesia dan memuatnya ke basis data.

**Kriteria penerimaan**
- Pengguna dapat mengunggah berkas `.xlsx` berukuran sampai 25 MB
- Sistem mendeteksi tahun edisi secara otomatis dari isi berkas
- Parsing berjalan sebagai queued job, bukan di dalam siklus request
- Antarmuka menampilkan status kemajuan tanpa perlu memuat ulang halaman
- Berkas dengan struktur tidak dikenali ditolak dengan pesan yang menjelaskan letak masalahnya
- Mengimpor berkas yang sama dua kali tidak menghasilkan data ganda
- Aplikasi menyediakan berkas contoh yang sudah tertanam, sehingga penilai dapat mencoba tanpa mengunduh apa pun

**Catatan teknis**
Header bertingkat dan sel ter-merge. Baca dengan `header=None`, lalu forward-fill baris 6 dan 7 untuk merekonstruksi nama indikator setiap kolom. Fungsi `pd.read_excel` dengan pengaturan bawaan akan menghasilkan keluaran kacau.

---

### F2 — Profil Capaian Daerah · P0

Menampilkan kondisi mutu pendidikan satu kabupaten/kota.

**Kriteria penerimaan**
- Pengguna memilih provinsi, kabupaten/kota, jenjang, dan status satuan pendidikan
- Sistem menampilkan seluruh indikator yang tersedia di level kabupaten/kota untuk kombinasi tersebut
- Indikator dikelompokkan menurut dimensi induknya (A, B, C, D, E)
- Setiap indikator menampilkan label capaian, arah perubahan, dan definisi ambangnya
- Indikator bernilai `Tidak Tersedia` ditampilkan terpisah, tidak dicampur dengan yang bermasalah
- Ringkasan atas menampilkan jumlah indikator merah, kuning, dan hijau

---

### F3 — Deteksi dan Prioritisasi Masalah · P0

Menentukan indikator mana yang paling mendesak ditangani.

**Kriteria penerimaan**
- Sistem menandai seluruh indikator berlabel merah dan kuning
- Setiap indikator bermasalah diberi skor prioritas
- Daftar diurutkan dari prioritas tertinggi
- Setiap entri disertai kalimat penjelas yang dapat dibaca tanpa latar belakang teknis
- Pengguna dapat melihat komponen pembentuk skor prioritas

**Contoh keluaran**

> **A.1 Kemampuan literasi — Prioritas 1 (skor 87)**
> Berlabel Kurang dan menurun dibanding tahun lalu. Berada di peringkat 31 dari 38 kabupaten/kota di Jawa Timur. Indikator ini memengaruhi 4 indikator turunan yang juga bermasalah.

---

### F4 — Analisis Akar Masalah · P0

Menelusuri dari gejala ke penyebab.

**Kriteria penerimaan**
- Untuk setiap indikator prioritas, sistem menampilkan kandidat akar masalah
- Kandidat ditentukan dari pohon keputusan yang memeriksa indikator pendukung
- Setiap kandidat menampilkan bukti berupa indikator yang mendasarinya
- Bila bukti tidak cukup, sistem menyatakannya terbuka, bukan memaksakan kesimpulan
- Aturan pemetaan tersimpan sebagai konfigurasi, bukan tertanam di kode

**Contoh alur**

```
A.1 Kemampuan literasi = Kurang
  ├─ Periksa D.1 Kualitas pembelajaran      → Sedang
  ├─ Periksa D.2 Refleksi dan perbaikan     → Kurang   ← bukti
  ├─ Periksa D.4 Iklim keamanan             → Baik
  └─ Periksa A.3 Karakter                   → Sedang

  Akar masalah terkuat: lemahnya budaya refleksi dan perbaikan
  pembelajaran oleh guru (D.2 Kurang, sejalan dengan D.1 Sedang)
```

---

### F5 — Perbandingan Antardaerah · P0

Menempatkan capaian daerah dalam konteks.

**Kriteria penerimaan**
- Menampilkan peringkat kabupaten/kota terhadap seluruh daerah dalam provinsi yang sama
- Menampilkan pembanding terhadap agregat provinsi dan nasional
- Tersedia tabel peringkat yang dapat diurutkan menurut indikator mana pun
- Perbandingan hanya dilakukan pada jenjang dan status yang sama

Fitur ini tidak tersedia di portal resmi. Ini pembeda utama produk.

---

### F6 — Analisis Tren Lintas Tahun · P1

Memanfaatkan empat edisi berkas yang tersedia.

**Kriteria penerimaan**
- Menampilkan pergerakan label capaian dari 2022 sampai 2025
- Menandai indikator yang memburuk dua tahun berturut-turut
- Menandai indikator yang membaik konsisten sebagai praktik yang layak dipertahankan
- Grafik dapat dibaca tanpa penjelasan tambahan

Nilai jual tambahan: portal resmi menampilkan capaian satu tahun. Produk ini menampilkan lintasannya.

---

### F7 — Generator Rencana Tindak Lanjut · P0

Mengubah analisis menjadi dokumen kerja.

**Kriteria penerimaan**
- Sistem menyusun draf rencana berisi masalah, akar masalah, kegiatan usulan, penanggung jawab, indikator keberhasilan, dan perkiraan waktu
- Pengguna dapat menyunting setiap butir sebelum diekspor
- Pengguna dapat menghapus dan menambah kegiatan
- Draf tersimpan dan dapat dibuka kembali

**Format keluaran per baris**

| Kolom | Contoh |
|---|---|
| Masalah | Kemampuan literasi SMP berlabel Kurang |
| Akar masalah | Budaya refleksi dan perbaikan pembelajaran lemah |
| Kegiatan | Pendampingan komunitas belajar antarsekolah, 12 sekolah prioritas |
| Penanggung jawab | Bidang Pembinaan SMP |
| Indikator keberhasilan | D.2 naik dari Kurang ke Sedang pada AN berikutnya |
| Perkiraan waktu | Triwulan I–II |

---

### F8 — Ekspor · P0

**Kriteria penerimaan**
- Ekspor PDF berisi profil daerah, analisis akar masalah, dan rencana tindak lanjut
- Ekspor Excel berisi data mentah hasil analisis
- Dokumen memuat sumber data dan tanggal pengambilan
- Berkas PDF dapat dibuka tanpa kesalahan tampilan pada peramban umum

---

### F9 — Autentikasi dan Peran · P1

Tiga peran: Admin (dapat mengimpor berkas daerah), Analis Dinas (menganalisis daerah dan menyusun rencana), dan Kepala Sekolah (mengunggah berkas sekolahnya sendiri dan menganalisisnya). Untuk keperluan penilaian, sediakan akun demo untuk ketiganya dan cantumkan di README.

---

### F10 — Mode Satuan Pendidikan · P1

Kepala sekolah mengunggah berkas Rapor Pendidikan sekolahnya sendiri dan memperoleh analisis dengan logika yang sama.

**Kriteria penerimaan**
- Halaman unggah terpisah dengan penjelasan cara memperoleh berkas dari akun belajar.id
- Sistem mendeteksi apakah berkas bertipe daerah atau satuan pendidikan
- Berkas yang tidak dikenali ditolak dengan pesan yang menjelaskan format yang diharapkan
- Bila berkas berhasil diproses, seluruh fitur F3, F4, F7, dan F8 berlaku sama
- Perbandingan (F5) untuk mode ini membandingkan sekolah terhadap agregat kabupaten dan provinsi, bukan terhadap sekolah lain, karena data sekolah lain tidak tersedia
- Berkas yang diunggah pengguna tidak dibagikan ke pengguna lain

**Batas pengujian**
Bila tim tidak memperoleh berkas contoh sebelum 2 September, halaman unggah tetap dibangun dengan validasi struktur, namun status fitur ditulis sebagai belum diuji dengan berkas asli. Nyatakan ini di proposal dan saat presentasi.

---

## 7. Algoritma Inti

### 7.1 Skor prioritas

```
Skor Prioritas = (40 × Bobot Label)
               + (25 × Bobot Perubahan)
               + (20 × Bobot Posisi Relatif)
               + (15 × Bobot Dampak Turunan)
```

| Komponen | Aturan |
|---|---|
| Bobot Label | Kurang = 1,0 · Sedang = 0,5 · Baik = 0 |
| Bobot Perubahan | Turun = 1,0 · Tidak berubah = 0,5 · Naik = 0 |
| Bobot Posisi Relatif | Persentil terbalik terhadap kabupaten/kota lain dalam provinsi |
| Bobot Dampak Turunan | Proporsi indikator anak yang juga bermasalah |

Seluruh bobot disimpan di `config/akar.php` dan dapat disesuaikan pengguna. Setiap skor dapat ditelusuri kembali ke komponen pembentuknya.

**Alasan desain:** juri hampir pasti menanyakan akuntabilitas algoritma. Jawabannya harus berupa rumus terbuka yang bisa diperiksa, bukan model yang tidak bisa dijelaskan. Untuk kasus ini, sistem berbasis aturan lebih tepat daripada pembelajaran mesin.

### 7.2 Pohon keputusan akar masalah

Struktur konfigurasi:

```php
// config/intervensi.php
'A.1' => [
    'nama' => 'Kemampuan literasi',
    'kandidat_akar' => [
        [
            'kode' => 'pembelajaran',
            'label' => 'Kualitas praktik pembelajaran belum optimal',
            'periksa' => ['D.1', 'D.2', 'D.3'],
            'ambang' => 'minimal_satu_kurang',
            'kegiatan' => ['literasi_komunitas_belajar', 'pendampingan_pengawas'],
        ],
        [
            'kode' => 'iklim',
            'label' => 'Iklim satuan pendidikan menghambat pembelajaran',
            'periksa' => ['D.4', 'D.8', 'D.10'],
            'ambang' => 'minimal_satu_kurang',
            'kegiatan' => ['penguatan_tppk', 'sosialisasi_iklim_aman'],
        ],
    ],
],
```

Cakupan MVP: sekitar 15 sampai 20 indikator prioritas yang dipetakan, bukan seluruh 274. Pilih yang paling sering merah dan paling berdampak. Untuk indikator yang belum dipetakan, sistem menampilkan capaian tanpa rekomendasi, dan menyatakannya secara jujur.

### 7.3 Katalog kegiatan

Setiap kode kegiatan merujuk ke entri di `config/kegiatan.php` yang memuat nama kegiatan, deskripsi, penanggung jawab tipikal, indikator keberhasilan, dan perkiraan waktu. Kegiatan disusun mengacu pada praktik yang direkomendasikan dalam kerangka Perencanaan Berbasis Data.

---

## 8. Arsitektur

### 8.1 Tumpukan teknologi

| Lapisan | Pilihan | Alasan |
|---|---|---|
| Framework | Laravel 11 | Dikuasai tim, ekosistem lengkap |
| Antarmuka | Blade + Livewire 3 | Dasbor interaktif tanpa menulis JavaScript |
| Gaya | Tailwind CSS | Cepat, konsisten |
| Basis data | PostgreSQL | Kueri agregasi dan peringkat lebih nyaman |
| Parsing Excel | maatwebsite/excel | Mendukung baca bertahap untuk berkas besar |
| Grafik | Chart.js | Ringan, cukup untuk kebutuhan |
| PDF | barryvdh/laravel-dompdf | Sederhana, tidak butuh peramban tanpa kepala |
| Antrean | Laravel Queue driver database | Tidak perlu Redis untuk skala demo |
| Peran | spatie/laravel-permission | Standar, hemat waktu |
| Deployment | Railway atau Fly.io | Gratis, deploy dari Git |

Keputusan yang perlu dipertahankan saat ditanya: tidak memakai pembelajaran mesin karena masalahnya bersifat aturan dan ambang batasnya sudah ditetapkan resmi; menggunakan model berbasis aturan justru meningkatkan akuntabilitas.

### 8.2 Struktur service

```
app/Services/Akar/
├── Parsers/
│   ├── MetadataIndikatorParser.php     Sheet Metadata → tabel indikator
│   ├── CapaianDaerahParser.php         Sheet provinsi → tabel capaian
│   └── HeaderResolver.php              Rekonstruksi header bertingkat
├── Analysis/
│   ├── PrioritasCalculator.php         Skor prioritas
│   ├── AkarMasalahAnalyzer.php         Pohon keputusan
│   ├── BenchmarkService.php            Peringkat antardaerah
│   ├── TrenService.php                 Analisis lintas tahun
│   └── PenjelasGenerator.php           Kalimat penjelas
└── Output/
    ├── RencanaAksiGenerator.php        Draf rencana tindak lanjut
    └── LaporanExporter.php             PDF dan Excel
```

Controller tidak berisi logika analisis. Ini dinilai pada aspek Code Project.

### 8.3 Skema basis data

```
indikator
  id, nomor (A.1), nama, dimensi (A), induk_id, jenis_layanan,
  definisi_konseptual, definisi_operasional,
  label_merah, definisi_merah, label_kuning, definisi_kuning,
  label_hijau, definisi_hijau,
  tersedia_satuan, tersedia_kabkota, tersedia_provinsi

wilayah
  id, provinsi, kabupaten_kota, level (nasional|provinsi|kabkota)

impor_berkas
  id, nama_berkas, tahun_edisi, status, jumlah_baris, hash_berkas,
  diproses_pada

capaian
  id, impor_id, wilayah_id, indikator_id, tahun,
  jenis_satuan, status_satuan, label_capaian, perubahan_nilai
  index: (wilayah_id, indikator_id, tahun, jenis_satuan, status_satuan)

analisis
  id, wilayah_id, tahun, jenis_satuan, status_satuan, dibuat_pada

analisis_prioritas
  id, analisis_id, indikator_id, skor, komponen_skor (json), kalimat_penjelas

analisis_akar
  id, analisis_prioritas_id, kode_akar, label, bukti (json), keyakinan

rencana_aksi
  id, analisis_id, judul, dibuat_oleh, dibuat_pada

rencana_aksi_item
  id, rencana_aksi_id, masalah, akar_masalah, kegiatan,
  penanggung_jawab, indikator_keberhasilan, perkiraan_waktu, urutan
```

Tabel `capaian` akan berisi ratusan ribu baris setelah empat edisi diimpor. Pastikan indeks komposit terpasang sejak awal.

---

## 9. Kebutuhan Non-Fungsional

| Aspek | Target |
|---|---|
| Waktu parsing satu berkas | Selesai di bawah 5 menit sebagai queued job |
| Waktu muat halaman profil daerah | Di bawah 2 detik |
| Ketersediaan tautan deployment | Aktif penuh selama masa penilaian (syarat wajib guidebook) |
| Peramban | Chrome, Firefox, Edge versi terkini |
| Responsif | Berfungsi pada layar 1280 piksel ke atas; layar kecil tidak diprioritaskan karena pengguna bekerja di komputer kantor |
| Privasi | Tidak ada data pribadi yang diproses. Seluruh data bersifat agregat wilayah |
| Penanganan galat | Setiap kegagalan parsing mencatat baris dan kolom penyebabnya |

---

## 10. Rencana Kerja

Delapan hari tersisa. Fitur dibekukan pada hari keenam tanpa pengecualian.

### 10.1 Pembagian peran

| Anggota | Tanggung jawab utama |
|---|---|
| A | Parser, skema basis data, queued job |
| B | Livewire, dasbor, grafik, ekspor |
| C | Konfigurasi intervensi dan katalog kegiatan, proposal, video |

Anggota C mengerjakan proposal secara paralel sejak hari kedua. Jangan menunggu aplikasi selesai.

### 10.2 Jadwal

| Hari | Tanggal | Target |
|---|---|---|
| 1 | 30 Agu | Kunci nama dan ruang lingkup. Daftar Gelombang 2. Repo, migrasi, skema. Pelajari struktur berkas selama 1 jam bersama. |
| 2 | 31 Agu | `MetadataIndikatorParser` selesai dan teruji. Tabel indikator terisi 274 baris. Autentikasi dan peran. Proposal bab 1–3. |
| 3 | 1 Sep | `CapaianDaerahParser` dan `HeaderResolver` selesai. Satu provinsi berhasil diimpor penuh. Proposal bab 4–5. |
| 4 | 2 Sep | Impor seluruh 38 provinsi. Halaman profil daerah (F2). `PrioritasCalculator` (F3). Proposal bab 6–8. |
| 5 | 3 Sep | Analisis akar masalah (F4). Perbandingan antardaerah (F5). Deploy pertama. Proposal bab 9–10. Anggota C mulai mencari berkas contoh Rapor Pendidikan sekolah, batas waktu hari ini. |
| 6 | 4 Sep | **Fitur dibekukan.** Generator rencana aksi (F7), ekspor (F8). Halaman unggah mode sekolah (F10) hanya jika F1–F8 sudah tuntas. Perbaikan galat, keadaan kosong, keadaan galat. README lengkap. Rekam video. |
| 7 | 5 Sep | Sunting video maksimal 10 menit. Finalisasi proposal, surat orisinalitas, periksa batas 30 halaman dan 20 MB. Rapikan tautan Google Drive dengan akses anyone with the link can view. |
| 8 | 6 Sep | **Kumpulkan.** Uji seluruh tautan dari perangkat dan jaringan berbeda. Minta orang di luar tim mencoba aplikasi dari nol memakai akun demo. |
| — | 7 Sep | Cadangan. Tidak mengubah apa pun kecuali ada yang rusak. |

### 10.3 Aturan yang tidak boleh dilanggar

Jika pada akhir hari ketiga parser belum berhasil membaca satu provinsi penuh, hentikan pengembangan fitur lain dan seluruh tim fokus ke parser. Tanpa parser yang bekerja, tidak ada produk.

---

## 11. Pemetaan ke Rubrik Penilaian

### Babak penyisihan (bobot 40%)

| Aspek | Bobot | Cara memenuhi |
|---|---|---|
| Kesesuaian dengan tema | 30% | Sebutkan subtema Pendidikan secara eksplisit. Hubungkan nama AKAR dengan tema "Where Ideas Take Root" di bab Latar Belakang |
| Inovasi dan kreativitas | 30% | Tekankan tiga hal yang tidak ada di portal resmi: perbandingan antardaerah, analisis tren lintas tahun, generator rencana aksi |
| Kelayakan dan implementasi | 15% | Tautan deployment aktif, berkas contoh tertanam, akun demo tersedia |
| Manfaat dan dampak | 15% | Kaitkan dengan 497.138 satuan pendidikan yang mengikuti AN dan anggaran Rp52,12 triliun |
| Sistematika penulisan | 10% | Ikuti persis 11 bagian format Lampiran A guidebook. Sisihkan satu sesi khusus periksa EYD |

### Babak final (bobot 60%)

| Aspek | Bobot | Cara memenuhi |
|---|---|---|
| Fungsionalitas dan kinerja | 30% | Demo impor berkas asli tanpa jeda, dari unggah sampai ekspor PDF |
| Antarmuka dan pengalaman | 10% | Konsisten, keadaan kosong dan galat tertangani |
| Code Project | 20% | Service class terpisah, konfigurasi tidak tertanam di kode, README lengkap, riwayat commit rapi |
| Presentasi dan kolaborasi | 15% | Pembagian peran jelas, setiap anggota bicara sesuai bagiannya |
| Argumentasi dan tanya jawab | 25% | Lihat bagian 12 |

---

## 12. Antisipasi Pertanyaan Juri

**"Dari mana datanya, dan apakah bisa diverifikasi?"**
Portal Satu Data Kemendikdasmen, `data.kemendikdasmen.go.id`. Terbuka tanpa akun. Kami mengunduhnya pada 30 Agustus 2026 dan tautan sumber tercantum di aplikasi.

**"Apa bedanya dengan platform Rapor Pendidikan resmi?"**
Portal resmi memberi diagnosis untuk satu daerah pada satu tahun. Kami menambahkan tiga hal yang tidak ada di sana: peringkat terhadap daerah lain, lintasan capaian empat tahun, dan draf dokumen perencanaan yang siap disunting.

**"Bagaimana kalian menentukan indikator ini merah atau tidak?"**
Kami tidak menentukannya. Ambang merah, kuning, dan hijau sudah didefinisikan Kemendikdasmen dalam sheet Metadata berkas resmi, lengkap dengan definisi operasionalnya. Sistem membaca definisi itu, tidak mengarang kriteria sendiri.

**"Kenapa tidak memakai kecerdasan buatan untuk analisisnya?"**
Karena masalahnya bersifat aturan dengan ambang yang sudah baku, dan karena keluarannya dipakai untuk mengalokasikan anggaran publik. Model yang tidak bisa dijelaskan justru menurunkan kelayakannya. Setiap skor dalam sistem kami dapat ditelusuri sampai ke komponen pembentuknya.

**"Bagaimana kalau format berkasnya berubah tahun depan?"**
Pemetaan indikator dan aturan intervensi disimpan sebagai berkas konfigurasi, bukan tertanam di kode. Menyesuaikan perubahan tidak memerlukan pengubahan logika.

**"Apa yang benar-benar dikerjakan tim, mengingat AI diperbolehkan?"**
Guidebook memang mengizinkan AI sebagai alat bantu, dengan syarat seluruh ide dan implementasi dapat dipertanggungjawabkan. Perancangan skema skoring, pohon keputusan, model data, dan arsitektur adalah karya tim. Setiap anggota siap menjelaskan bagian kodenya masing-masing.

**"Siapa yang akan memakai ini, dan bagaimana keberlanjutannya?"**
Dinas Pendidikan kabupaten/kota sebagai pengguna utama, dan kepala sekolah sebagai pengguna level kedua. Kanal adopsinya sudah ada dan dibiayai: setiap daerah dan satuan pendidikan wajib menyusun perencanaan berbasis data, dan Asesmen Nasional 2026 mencakup 497.138 satuan pendidikan.

**"Kenapa dinas, bukan langsung guru dan sekolah?"**
Keduanya kami layani, tetapi jalur demo memakai data daerah karena alasan ketersediaan. Rapor Pendidikan level kabupaten/kota terbuka tanpa akun sehingga Bapak dan Ibu bisa memverifikasi sumbernya sekarang juga. Versi satuan pendidikan hanya dapat diunduh pemilik akun belajar.id sekolah tersebut, sehingga tidak etis dan tidak mungkin kami pakai sebagai bahan demo publik. Karena logika analisisnya identik, sekolah cukup mengunggah berkasnya sendiri untuk mendapat hasil yang sama.

**"Mode sekolahnya sudah pernah diuji?"**
Jawab sesuai kondisi sebenarnya. Jika sudah memperoleh berkas contoh, tunjukkan. Jika belum, katakan terus terang bahwa alur unggah dan validasi sudah dibangun namun belum diuji dengan berkas asli karena keterbatasan akses. Kejujuran di titik ini lebih dihargai daripada klaim yang bisa dibongkar dengan satu pertanyaan lanjutan.

---

## 13. Risiko

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Parser gagal membaca header bertingkat | Fatal | Kerjakan paling awal. Batas waktu akhir hari ketiga |
| Struktur sheet berbeda antaredisi | Tinggi | Bangun `HeaderResolver` yang mencari berdasarkan pola nama, bukan posisi kolom tetap |
| Waktu habis untuk memetakan 274 indikator | Sedang | Batasi cakupan MVP ke 15–20 indikator prioritas. Nyatakan batasan ini secara jujur di proposal |
| Deployment mati saat penilaian | Fatal | Deploy pada hari kelima, pantau harian. Siapkan cadangan penyedia kedua |
| Berkas 21 MB memperlambat demo | Sedang | Sediakan basis data yang sudah terisi. Demo impor pakai berkas satu provinsi yang lebih kecil |
| Anggota tim tidak paham kode yang ditulis anggota lain | Tinggi | Sesi penjelasan silang 30 menit pada hari keenam |
| Berkas Rapor Pendidikan satuan pendidikan tidak diperoleh | Rendah | Mode sekolah tetap dibangun dengan validasi struktur. Status belum diuji dinyatakan terbuka. Tidak memengaruhi jalur demo utama |
| Struktur berkas sekolah ternyata jauh berbeda | Rendah | Turunkan F10 menjadi peta jalan. Karena berstatus P1, tidak mengganggu MVP |

---

## 14. Peta Jalan Pascakompetisi

| Tahap | Isi |
|---|---|
| Jangka pendek | Uji mode sekolah dengan berkas asli dari beberapa satuan pendidikan. Perluas pemetaan dari 15–20 indikator ke seluruh 274 |
| Jangka menengah | Uji coba bersama satu Dinas Pendidikan. Dukungan multi-tenant untuk banyak dinas dan sekolah. Integrasi data anggaran dari Neraca Pendidikan Daerah |
| Jangka panjang | Pembanding antarsekolah dalam satu kabupaten, bila kerja sama resmi dengan dinas memungkinkan akses data agregat sekolah |

---

## Lampiran — Berkas Rujukan

| Berkas | Keterangan |
|---|---|
| `01_rapor_pendidikan_indonesia/` | 4 edisi Rapor Pendidikan Indonesia |
| `METADATA_INDIKATOR_RAPOR_PENDIDIKAN.csv` | 274 indikator dengan ambang resmi |
| `02_indikator_prioritas/` | 288 berkas indikator prioritas 2021–2025 |
| `KATALOG_464_DATASET.csv` | Katalog lengkap dengan URL sumber |
| `unduh.py` | Skrip pengunduh dari API portal |

Sumber: Kementerian Pendidikan Dasar dan Menengah, Portal Satu Data Kemendikdasmen, diakses 30 Agustus 2026.

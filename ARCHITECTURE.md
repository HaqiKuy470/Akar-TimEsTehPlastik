# ARCHITECTURE.md — AKAR

Arsitektur teknis. Turunan dari `PRD-AKAR.md` dengan penyesuaian untuk lingkungan produksi cPanel.

---

## 1. Perubahan dari PRD

Target deployment berubah dari Railway/Fly.io menjadi **cPanel shared hosting**. Dua keputusan di PRD ikut berubah:

| Butir | PRD | Sekarang | Alasan |
|---|---|---|---|
| Basis data | PostgreSQL | **MySQL 8 / MariaDB** | cPanel umumnya hanya menyediakan MySQL |
| Antrean | Queue worker berjalan terus | **Cron dengan `--stop-when-empty`** | Supervisor tidak tersedia di shared hosting |

Satu keputusan arsitektur baru ditambahkan, dijelaskan di bagian 4: **pemisahan proses impor dari proses penyajian.**

---

## 2. Tumpukan Teknologi

| Lapisan | Pilihan | Alasan |
|---|---|---|
| Framework | Laravel 11, PHP 8.2 | Dikuasai tim, tersedia di mayoritas cPanel |
| Antarmuka | Blade + Livewire 3 | Tim sudah terbiasa. Dasbor interaktif tanpa menulis JavaScript |
| Gaya | Tailwind CSS 3 | Dibangun di lokal, hasil ikut di-commit |
| Basis data | MySQL 8 / MariaDB 10.6 | Batasan lingkungan |
| Parsing XLSX | `maatwebsite/excel` | Mendukung `WithChunkReading` untuk berkas besar |
| Peran | `spatie/laravel-permission` | Standar, hemat waktu |
| PDF | `barryvdh/laravel-dompdf` | Tidak memerlukan peramban tanpa kepala, cocok untuk shared hosting |
| Grafik | Chart.js via CDN | Menghindari kompleksitas bundling |
| Antrean | Driver `database` | Redis tidak tersedia |
| Cache dan sesi | Driver `database` | Sama |

Dependensi sengaja dijaga minimal. Setiap paket tambahan adalah risiko kegagalan di lingkungan yang tidak bisa kita kendalikan penuh.

---

## 3. Struktur Data Sumber

### 3.1 Berkas Rapor Pendidikan Indonesia

Empat edisi tersedia, masing-masing 16–21 MB, memuat data tahun 2022 sampai 2025.

| Sheet | Isi |
|---|---|
| `Metadata` | 274 indikator |
| `Nasional` | Agregat nasional |
| 38 sheet provinsi | Aceh sampai Sumatera Utara |

### 3.2 Anatomi sheet provinsi

Contoh Jawa Timur: 951 baris × 548 kolom.

```
Baris 1   DATA HASIL RAPOR PENDIDIKAN ...
Baris 2   Berdasarkan data pendidikan ...
Baris 3   (kosong)
Baris 4   Catatan: untuk Rapor Pendidikan ...
Baris 5   (kosong)
Baris 6   [    kosong sampai kolom D    ] A.1 |     | A.1 |     | ...
Baris 7   [    kosong sampai kolom D    ] A.1 Kemampuan literasi | | A.1.1 Kompetensi membaca teks informasi | | ...
Baris 8   Provinsi | Kabupaten/Kota | Jenis Satuan Pendidikan | Status Satuan Pendidikan | Label Capaian 2025 | Perubahan Nilai Capaian | Label Capaian 2025 | Perubahan Nilai Capaian | ...
Baris 9+  Jawa Timur | - | SMK Umum | Semua (Negeri dan Swasta) | Sedang | Naik | Tidak Tersedia | Naik | ...
```

Kolom A–D adalah dimensi. Kolom E dan seterusnya berpasangan dua kolom per indikator.

Baris 6 dan 7 memiliki sel ter-merge, sehingga hanya kolom pertama tiap kelompok yang berisi nilai. Sisanya kosong dan harus diisi dengan forward-fill.

**Domain nilai:**

| Kolom | Nilai yang mungkin |
|---|---|
| Label Capaian | `Baik`, `Sedang`, `Kurang`, `Tidak Tersedia` |
| Perubahan Nilai Capaian | `Naik`, `Turun`, `Tidak berubah`, `Tidak Tersedia` |
| Jenis Satuan Pendidikan | SD Umum, MI, SMP Umum, MTs, SMA Umum, MA, SMK Umum, SLB, SMPLB, SMALB, Pendidikan Keagamaan, Kesetaraan, dan turunannya |
| Status Satuan Pendidikan | `Negeri`, `Swasta`, `Semua (Negeri dan Swasta)` |

Baris dengan `Kabupaten/Kota` bernilai `-` adalah **agregat provinsi**, bukan data kabupaten. Simpan sebagai wilayah level provinsi dan pakai sebagai pembanding.

### 3.3 Sheet Metadata

274 baris, header di baris 4, data mulai baris 5. Kolomnya:

`Jenis Layanan/Jenjang Pendidikan`, `Nomor Indikator`, `Nama Indikator`, `Definisi Konseptual`, `Definisi Operasional Daerah`, `Sumber Data`, `Label Merah`, `Definisi Label Merah`, `Label Kuning`, `Definisi Label Kuning`, `Label Hijau`, `Definisi Label Hijau`, `Ketersediaan Indikator di Tingkat Satuan Pendidikan`, `Ketersediaan Indikator di Tingkat Kabupaten/Kota`, `Ketersediaan Indikator di Tingkat Provinsi`

Ambang merah, kuning, dan hijau sudah ditetapkan resmi oleh Kemendikdasmen. Sistem membaca definisi ini, tidak mengarang kriteria sendiri. Tiga kolom terakhir menentukan indikator mana yang valid dipakai pada tiap level dan harus dijadikan filter.

---

## 4. Keputusan Arsitektur Utama

### 4.1 Impor dipisahkan dari penyajian

**Masalah.** Berkas provinsi berukuran 21 MB dengan 38 sheet. Memproses di server cPanel akan menabrak batas memori 128–256 MB, batas waktu eksekusi, dan batas ukuran unggah yang sering di bawah 21 MB.

**Keputusan.** Parsing berkas daerah dilakukan **di mesin lokal** lewat artisan command. Hasilnya dikirim ke produksi sebagai dump SQL.

```
Mesin lokal                          Produksi cPanel
───────────                          ───────────────
berkas XLSX 21 MB
      │
      ▼
php artisan akar:impor
      │
      ▼
MySQL lokal terisi
      │
      ▼
mysqldump ──────────────────────────▶ impor via phpMyAdmin
                                              │
                                              ▼
                                      Aplikasi menyajikan analisis
```

**Konsekuensi.**
- Server produksi tidak pernah memproses berkas besar. Risiko kehabisan memori hilang.
- Demo saat penilaian memakai basis data yang sudah terisi, sehingga cepat dan tidak bergantung pada kecepatan unggah di lokasi.
- Fitur unggah tetap ada di produksi, tetapi diperuntukkan bagi berkas satuan pendidikan yang jauh lebih kecil, dan bagi berkas provinsi tunggal bila diperlukan.
- Saat presentasi, tunjukkan artisan command sebagai bagian dari alur kerja. Ini justru memperlihatkan kematangan rekayasa, bukan kekurangan.

### 4.2 Antrean lewat cron

Supervisor tidak tersedia. Konfigurasi cron di cPanel:

```
* * * * * cd /home/USER/akar && php artisan schedule:run >> /dev/null 2>&1
```

Di `routes/console.php`:

```php
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=2')
    ->everyMinute()
    ->withoutOverlapping();
```

`--stop-when-empty` membuat proses berhenti setelah antrean kosong. `--max-time=50` mencegah proses tumpang tindih dengan cron menit berikutnya. `withoutOverlapping` sebagai lapis pengaman kedua.

Jeda maksimal satu menit sebelum job dieksekusi. Untuk kasus ini, dapat diterima. Antarmuka menampilkan status "Menunggu diproses" secara jujur.

### 4.3 Parsing bertahap, tidak pernah memuat penuh

Gunakan `WithChunkReading` dari `maatwebsite/excel` dengan ukuran potongan 200 baris. Satu sheet provinsi diproses sebagai satu job terpisah, sehingga 38 sheet menjadi 38 job. Bila satu sheet gagal, sisanya tetap berhasil dan sheet yang gagal dapat diulang.

### 4.4 Analisis berbasis aturan, bukan pembelajaran mesin

Keluaran sistem dipakai untuk mengalokasikan anggaran publik. Model yang tidak dapat dijelaskan menurunkan kelayakan produk, bukan meningkatkannya. Ambang penilaian sudah ditetapkan resmi di sheet Metadata, sehingga masalahnya memang bersifat aturan.

Setiap skor mengembalikan rincian komponennya, disimpan sebagai kolom JSON, dan dapat ditampilkan ke pengguna.

---

## 5. Skema Basis Data

```sql
indikator
  id                    BIGINT PK
  nomor                 VARCHAR(16)   -- 'A.1', 'A.1.1', 'D.2'
  induk_id              BIGINT NULL FK indikator.id
  dimensi               CHAR(1)       -- 'A'..'E'
  nama                  VARCHAR(255)
  jenis_layanan         VARCHAR(128)
  definisi_konseptual   TEXT
  definisi_operasional  TEXT
  sumber_data           VARCHAR(128)
  label_merah           VARCHAR(64)
  definisi_merah        TEXT
  label_kuning          VARCHAR(64)
  definisi_kuning       TEXT
  label_hijau           VARCHAR(64)
  definisi_hijau        TEXT
  tersedia_satuan       BOOLEAN
  tersedia_kabkota      BOOLEAN
  tersedia_provinsi     BOOLEAN
  UNIQUE (nomor, jenis_layanan)

wilayah
  id                    BIGINT PK
  level                 ENUM('nasional','provinsi','kabkota','satuan')
  provinsi              VARCHAR(64) NULL
  kabupaten_kota        VARCHAR(96) NULL
  nama_satuan           VARCHAR(160) NULL   -- untuk mode sekolah
  induk_id              BIGINT NULL FK wilayah.id
  UNIQUE (level, provinsi, kabupaten_kota, nama_satuan)

impor_berkas
  id                    BIGINT PK
  nama_berkas           VARCHAR(255)
  jenis                 ENUM('daerah','satuan')
  tahun_edisi           SMALLINT
  hash_berkas           CHAR(64) UNIQUE     -- cegah impor ganda
  status                ENUM('antre','proses','selesai','gagal')
  jumlah_baris          INT
  catatan_galat         TEXT NULL
  diunggah_oleh         BIGINT NULL FK users.id
  diproses_pada         TIMESTAMP NULL

capaian
  id                    BIGINT PK
  impor_id              BIGINT FK impor_berkas.id
  wilayah_id            BIGINT FK wilayah.id
  indikator_id          BIGINT FK indikator.id
  tahun                 SMALLINT
  jenis_satuan          VARCHAR(64)
  status_satuan         VARCHAR(32)
  label_capaian         ENUM('Baik','Sedang','Kurang','Tidak Tersedia')
  perubahan_nilai       ENUM('Naik','Turun','Tidak berubah','Tidak Tersedia')
  INDEX idx_lookup (wilayah_id, tahun, jenis_satuan, status_satuan, indikator_id)
  INDEX idx_banding (indikator_id, tahun, jenis_satuan, status_satuan, label_capaian)

analisis
  id                    BIGINT PK
  wilayah_id            BIGINT FK wilayah.id
  tahun                 SMALLINT
  jenis_satuan          VARCHAR(64)
  status_satuan         VARCHAR(32)
  bobot_dipakai         JSON              -- salinan config saat dijalankan
  dibuat_oleh           BIGINT FK users.id
  dibuat_pada           TIMESTAMP

analisis_prioritas
  id                    BIGINT PK
  analisis_id           BIGINT FK analisis.id
  indikator_id          BIGINT FK indikator.id
  skor                  DECIMAL(5,2)
  komponen_skor         JSON              -- rincian per komponen
  kalimat_penjelas      TEXT
  peringkat             SMALLINT

analisis_akar
  id                    BIGINT PK
  analisis_prioritas_id BIGINT FK analisis_prioritas.id
  kode_akar             VARCHAR(64)
  label                 VARCHAR(255)
  bukti                 JSON              -- indikator pendukung + labelnya
  keyakinan             ENUM('kuat','sedang','lemah','tidak_cukup_bukti')

rencana_aksi
  id                    BIGINT PK
  analisis_id           BIGINT FK analisis.id
  judul                 VARCHAR(255)
  dibuat_oleh           BIGINT FK users.id
  timestamps

rencana_aksi_item
  id                    BIGINT PK
  rencana_aksi_id       BIGINT FK rencana_aksi.id
  masalah               VARCHAR(255)
  akar_masalah          VARCHAR(255)
  kegiatan              TEXT
  penanggung_jawab      VARCHAR(128)
  indikator_keberhasilan TEXT
  perkiraan_waktu       VARCHAR(64)
  urutan                SMALLINT
```

**Perkiraan volume.** Satu edisi berisi 38 provinsi × sekitar 950 baris × sekitar 135 indikator. Setelah dinormalisasi, tabel `capaian` dapat mencapai beberapa juta baris untuk empat edisi.

Untuk cPanel dengan kuota basis data terbatas, **impor hanya edisi 2024 dan 2025 ke produksi**. Edisi 2022 dan 2023 dipakai hanya bila fitur tren (F6) sempat dikerjakan, dan itu berprioritas P1. Batasi juga ke jenjang yang relevan bila kuota mepet.

---

## 6. Lapisan Service

```
app/Services/Akar/
├── Parsers/
│   ├── HeaderResolver.php
│   ├── MetadataIndikatorParser.php
│   ├── CapaianDaerahParser.php
│   └── CapaianSekolahParser.php
├── Analysis/
│   ├── PrioritasCalculator.php
│   ├── AkarMasalahAnalyzer.php
│   ├── BenchmarkService.php
│   ├── TrenService.php
│   └── PenjelasGenerator.php
└── Output/
    ├── RencanaAksiGenerator.php
    └── LaporanExporter.php
```

### 6.1 HeaderResolver

Komponen paling kritis. Tugasnya mengubah header bertingkat menjadi peta indeks kolom ke indikator.

Algoritma:

1. Baca baris 6, 7, dan 8 secara mentah
2. Forward-fill baris 6 dan 7 untuk mengisi sel bekas merge
3. Kolom 1 sampai 4 ditandai sebagai dimensi
4. Untuk kolom 5 dan seterusnya, ekstrak nomor indikator dari baris 7 dengan pola `^([A-E]\.\d+(\.\d+)*)`
5. Tentukan peran kolom dari baris 8: mengandung "Label Capaian" atau "Perubahan Nilai"
6. Keluarkan peta `['E' => ['nomor' => 'A.1', 'peran' => 'label'], 'F' => ['nomor' => 'A.1', 'peran' => 'perubahan'], ...]`

**Jangan mengandalkan posisi kolom tetap.** Susunan indikator berbeda antaredisi. Selalu resolusi berdasarkan pola nama.

Bila baris 7 tidak mengandung pola nomor indikator yang dikenali, lempar pengecualian dengan menyebut indeks kolom dan isi selnya. Pesan galat yang spesifik akan menghemat berjam-jam saat debugging di hari kelima.

### 6.2 PrioritasCalculator

```
Skor = (40 × bobotLabel)
     + (25 × bobotPerubahan)
     + (20 × bobotPosisi)
     + (15 × bobotDampakTurunan)
```

| Komponen | Aturan |
|---|---|
| bobotLabel | Kurang = 1.0, Sedang = 0.5, Baik = 0.0 |
| bobotPerubahan | Turun = 1.0, Tidak berubah = 0.5, Naik = 0.0 |
| bobotPosisi | Persentil terbalik terhadap kabupaten/kota lain dalam provinsi yang sama, jenjang dan status sama |
| bobotDampakTurunan | Proporsi indikator anak yang berlabel Kurang atau Sedang |

Indikator berlabel `Tidak Tersedia` dikecualikan dari perhitungan, tidak diberi skor nol.

Kembalikan objek berisi skor akhir dan array komponen. Simpan array itu di `analisis_prioritas.komponen_skor`.

Bobot dibaca dari `config/akar.php` dan disalin ke `analisis.bobot_dipakai` saat analisis dijalankan, sehingga hasil lama tetap dapat direproduksi bila konfigurasi berubah.

### 6.3 AkarMasalahAnalyzer

Membaca `config/intervensi.php`. Untuk tiap indikator prioritas, telusuri kandidat akar masalah, periksa indikator pendukungnya, kumpulkan bukti, lalu tetapkan tingkat keyakinan.

```php
// config/intervensi.php
return [
    'A.1' => [
        'nama' => 'Kemampuan literasi',
        'kandidat_akar' => [
            [
                'kode'     => 'pembelajaran',
                'label'    => 'Kualitas praktik pembelajaran belum optimal',
                'periksa'  => ['D.1', 'D.2', 'D.3'],
                'ambang'   => 'minimal_satu_kurang',
                'kegiatan' => ['literasi_komunitas_belajar', 'pendampingan_pengawas'],
            ],
            [
                'kode'     => 'iklim',
                'label'    => 'Iklim satuan pendidikan menghambat pembelajaran',
                'periksa'  => ['D.4', 'D.8', 'D.10'],
                'ambang'   => 'minimal_satu_kurang',
                'kegiatan' => ['penguatan_tppk', 'sosialisasi_iklim_aman'],
            ],
        ],
    ],
];
```

Tingkat keyakinan:

| Kondisi | Keyakinan |
|---|---|
| Dua atau lebih indikator pendukung berlabel Kurang | `kuat` |
| Satu berlabel Kurang, atau dua berlabel Sedang | `sedang` |
| Satu berlabel Sedang | `lemah` |
| Seluruh pendukung Baik atau Tidak Tersedia | `tidak_cukup_bukti` |

Bila hasilnya `tidak_cukup_bukti`, sampaikan apa adanya. Jangan memaksakan kesimpulan.

Cakupan MVP: 15–20 indikator yang paling sering berlabel merah. Untuk indikator di luar pemetaan, tampilkan capaian tanpa rekomendasi dan nyatakan status itu di antarmuka.

### 6.4 BenchmarkService

Peringkat dihitung dengan mengurutkan seluruh kabupaten/kota dalam provinsi yang sama, pada indikator, tahun, jenjang, dan status yang sama. Urutan label: Baik lebih tinggi daripada Sedang, Sedang lebih tinggi daripada Kurang. Indikator `Tidak Tersedia` dikeluarkan dari populasi pemeringkatan, bukan ditempatkan di posisi terbawah.

Untuk mode satuan pendidikan, pembanding adalah agregat kabupaten dan provinsi, bukan sekolah lain, karena data sekolah lain tidak tersedia untuk publik.

Gunakan kueri agregat dengan indeks `idx_banding`. Jangan menghitung peringkat di PHP dengan memuat seluruh baris.

---

## 7. Alur Utama

### 7.1 Impor berkas daerah, di lokal

```
php artisan akar:impor path/berkas.xlsx
      │
      ├─ hitung hash, cek duplikat
      ├─ buat record impor_berkas
      ├─ parse sheet Metadata → tabel indikator
      └─ untuk tiap sheet provinsi
             └─ dispatch ProsesSheetProvinsi
                    ├─ HeaderResolver → peta kolom
                    ├─ baca bertahap 200 baris
                    ├─ upsert wilayah
                    └─ sisip capaian secara massal
```

### 7.2 Menjalankan analisis

```
Pengguna memilih wilayah, tahun, jenjang, status
      │
      ├─ PrioritasCalculator      → analisis_prioritas
      ├─ AkarMasalahAnalyzer      → analisis_akar
      ├─ BenchmarkService         → peringkat
      └─ PenjelasGenerator        → kalimat penjelas
```

Analisis satu wilayah menyentuh ratusan baris, bukan jutaan. Cukup cepat untuk dijalankan dalam siklus request. Bila melebihi 3 detik, pindahkan ke queue dan tampilkan status.

### 7.3 Menyusun rencana aksi

```
Hasil analisis
      │
      ▼
RencanaAksiGenerator
      ├─ ambil kode kegiatan dari config/intervensi.php
      ├─ perkaya dari config/kegiatan.php
      └─ hasilkan draf item
      │
      ▼
Pengguna menyunting, menambah, menghapus
      │
      ▼
LaporanExporter → PDF atau Excel
```

---

## 8. Deployment cPanel

### 8.1 Struktur direktori

Laravel tidak dirancang untuk `public_html`. Susunan yang dipakai:

```
/home/USER/
├── akar/                    ← seluruh aplikasi Laravel
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   └── ...
└── public_html/             ← isi folder public Laravel
    ├── index.php            ← path diubah menunjuk ke ../akar
    ├── build/               ← hasil npm run build, ikut di-commit
    └── .htaccess
```

Ubah `public_html/index.php`:

```php
require __DIR__.'/../akar/vendor/autoload.php';
$app = require_once __DIR__.'/../akar/bootstrap/app.php';
```

### 8.2 Langkah pemasangan

1. Buat basis data MySQL dan pengguna lewat cPanel
2. Unggah kode. Bila Terminal tersedia, gunakan Git. Bila tidak, unggah arsip ZIP lalu ekstrak
3. `vendor/` ikut diunggah bila Composer tidak tersedia di server
4. Salin `.env`, isi kredensial, jalankan `php artisan key:generate` atau isi `APP_KEY` manual
5. Set `APP_DEBUG=false` dan `APP_ENV=production`
6. Jalankan migrasi. Bila CLI tidak tersedia, buat rute sementara terlindungi yang memanggil `Artisan::call('migrate')`, lalu **hapus rute itu setelah selesai**
7. Impor dump SQL hasil parsing lokal lewat phpMyAdmin
8. Pasang cron `schedule:run`
9. Aktifkan AutoSSL
10. Set izin `storage/` dan `bootstrap/cache/` ke 755

### 8.3 Berkas .env produksi

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda

DB_CONNECTION=mysql
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

FILESYSTEM_DISK=local
```

### 8.4 Penyesuaian batas PHP

Bila fitur unggah dipakai di produksi, tambahkan di `public_html/.htaccess` atau `.user.ini`:

```
php_value upload_max_filesize 32M
php_value post_max_size 36M
php_value memory_limit 256M
php_value max_execution_time 120
```

Sebagian penyedia mengunci nilai ini. Bila terkunci, andalkan jalur impor lokal di bagian 4.1 dan batasi unggah produksi hanya untuk berkas satuan pendidikan.

### 8.5 Daftar periksa sebelum penilaian

- [ ] Tautan produksi terbuka dari jaringan berbeda, bukan hanya dari komputer tim
- [ ] HTTPS aktif tanpa peringatan
- [ ] Akun demo untuk ketiga peran berfungsi
- [ ] Basis data terisi minimal edisi 2024 dan 2025
- [ ] Ekspor PDF menghasilkan berkas yang terbuka normal
- [ ] Cron berjalan, dibuktikan dengan satu job uji
- [ ] `APP_DEBUG=false`, tidak ada jejak galat yang bocor ke pengguna
- [ ] Rute migrasi sementara sudah dihapus
- [ ] Halaman galat 404 dan 500 tampil rapi
- [ ] Orang di luar tim berhasil memakai aplikasi dari nol dengan panduan README

---

## 9. Pengujian

| Lapisan | Cakupan |
|---|---|
| Unit | `HeaderResolver` dengan fixture berbagai variasi header. `PrioritasCalculator` dengan kasus batas. `AkarMasalahAnalyzer` untuk tiap tingkat keyakinan |
| Feature | Alur impor sampai selesai. Alur analisis. Alur ekspor |
| Manual | Satu berkas provinsi asli diimpor penuh, hasilnya dibandingkan dengan pembacaan manual pada beberapa baris acak |

Fixture disimpan di `tests/Fixtures/` sebagai berkas XLSX kecil buatan sendiri, bukan potongan berkas asli 21 MB.

Prioritas pengujian ada di parser. Komponen lain boleh diuji seadanya bila waktu mendesak, tetapi parser tanpa test adalah risiko yang tidak sepadan.

---

## 10. Risiko Teknis

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Header bertingkat gagal diurai | Fatal | Kerjakan paling awal. Batas waktu 1 September |
| Struktur berbeda antaredisi | Tinggi | Resolusi berdasarkan pola nama, bukan posisi kolom |
| Kuota basis data cPanel terlampaui | Tinggi | Impor hanya edisi 2024 dan 2025. Batasi jenjang bila perlu |
| Cron tidak berjalan di penyedia tertentu | Sedang | Sediakan jalur impor lokal sebagai cadangan utama. Fitur inti tidak bergantung pada antrean |
| Composer tidak tersedia di server | Sedang | Unggah `vendor/` hasil `composer install --no-dev --optimize-autoloader` dari lokal |
| Node.js tidak tersedia di server | Rendah | Aset dibangun di lokal, `public/build` ikut di-commit |
| Batas unggah lebih kecil dari berkas | Sedang | Jalur impor lokal. Unggah produksi hanya untuk berkas satuan pendidikan |
| Deployment mati saat penilaian | Fatal | Deploy pada 3 September, pantau harian sampai 7 September |

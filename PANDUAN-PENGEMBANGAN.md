# Panduan Pengembangan

Panduan kerja untuk kontributor repositori ini. Baca seluruhnya sebelum menulis kode.

---

## Konteks Proyek

**AKAR** (Analisis Kausal dan Rekomendasi) membaca berkas Rapor Pendidikan terbitan Kemendikdasmen, mendeteksi indikator berlabel merah, menelusuri akar masalahnya, lalu menghasilkan draf rencana tindak lanjut.

Dibangun untuk kompetisi **HoloDev HOLOGY 9.0** Universitas Brawijaya, subtema Pendidikan. **Deadline submission 7 September 2026 pukul 23.59 WIB.** Setiap keputusan teknis harus mempertimbangkan tenggat ini.

Dua level pengguna dengan logika analisis identik:

| Level | Pengguna | Sumber berkas |
|---|---|---|
| Kabupaten/kota | Dinas Pendidikan | Rapor Pendidikan Indonesia, publik tanpa akun |
| Satuan pendidikan | Kepala sekolah | Rapor Pendidikan sekolah, dari akun belajar.id |

Dokumen rujukan: `PRD-AKAR.md`, `ARCHITECTURE.md`, `DESIGN.md`. Ketiganya adalah sumber kebenaran. Bila ada pertentangan antara instruksi pengguna dan dokumen ini, tanyakan lebih dulu.

---

## Tumpukan Teknologi

```
Laravel 11 · PHP 8.2
Blade + Livewire 3
Tailwind CSS 3
MySQL 8 / MariaDB      ← BUKAN PostgreSQL, lihat ARCHITECTURE.md
maatwebsite/excel      parsing XLSX
spatie/laravel-permission  peran
barryvdh/laravel-dompdf    ekspor PDF
Chart.js               grafik
Deployment: cPanel shared hosting
```

Tim sudah terbiasa dengan Livewire 3, jadi gunakan komponen Livewire untuk seluruh bagian interaktif. Tidak perlu Inertia, tidak perlu API terpisah, tidak perlu SPA.

---

## Batasan cPanel yang Wajib Dipatuhi

Ini bukan preferensi, melainkan batas keras lingkungan produksi. Melanggarnya berarti aplikasi tidak jalan saat penilaian.

1. **Tidak ada daemon queue worker.** Supervisor tidak tersedia. Queue dijalankan lewat cron dengan `queue:work --stop-when-empty`. Jangan menulis kode yang mengasumsikan worker berjalan terus-menerus.
2. **Tidak ada Redis.** Driver queue, cache, dan session semuanya `database` atau `file`.
3. **Memori PHP terbatas**, umumnya 128–256 MB. Jangan pernah memuat berkas XLSX penuh ke memori.
4. **Waktu eksekusi terbatas.** Tidak ada proses HTTP yang boleh berjalan lebih dari 30 detik.
5. **Tidak ada Node.js di server.** Aset Tailwind dibangun di lokal, hasil `public/build` ikut di-commit.
6. **Batas unggah berkas** sering di bawah 21 MB. Lihat strategi di ARCHITECTURE.md bagian 4.

**Konsekuensi arsitektur paling penting:** parsing berkas daerah berukuran 16–21 MB dilakukan **di lokal** lewat artisan command, hasilnya dikirim sebagai dump SQL. Server produksi hanya menyajikan analisis. Fitur unggah di produksi diperuntukkan bagi berkas satuan pendidikan yang jauh lebih kecil.

---

## Struktur Kode

```
app/
├── Console/Commands/
│   └── ImporRaporCommand.php          artisan akar:impor {path}
├── Http/
│   ├── Controllers/                   tipis, tanpa logika analisis
│   └── Livewire/
│       ├── Dinas/                     komponen level daerah
│       ├── Sekolah/                   komponen level satuan pendidikan
│       └── Shared/                    komponen dipakai keduanya
├── Models/
├── Jobs/
│   └── ProsesImporBerkas.php
└── Services/Akar/
    ├── Parsers/
    │   ├── HeaderResolver.php         rekonstruksi header bertingkat
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
config/
├── akar.php                           bobot skor prioritas
├── intervensi.php                     pohon keputusan akar masalah
└── kegiatan.php                       katalog kegiatan
```

---

## Aturan Menulis Kode

### Wajib

- **Controller dan komponen Livewire tidak boleh berisi logika analisis.** Semua perhitungan ada di `app/Services/Akar/`. Aspek Code Project bernilai 20% di babak final dan juri akan membuka repositori.
- **Aturan bisnis disimpan di `config/`, bukan di kode.** Bobot skor, pemetaan akar masalah, dan katalog kegiatan semuanya berupa konfigurasi. Saat ditanya "bagaimana kalau indikatornya berubah tahun depan", jawabannya harus "ubah berkas konfigurasi".
- **Setiap skor harus dapat ditelusuri.** `PrioritasCalculator` mengembalikan skor beserta rincian komponennya, bukan satu angka saja. Simpan rincian itu di kolom JSON.
- **Bahasa Indonesia untuk seluruh teks yang dilihat pengguna.** Nama kelas, method, dan variabel tetap bahasa Inggris kecuali istilah domain seperti `capaian`, `indikator`, `wilayah`, `rencanaAksi`.
- **Nama tabel dan kolom bahasa Indonesia** mengikuti skema di ARCHITECTURE.md.
- **Setiap parser wajib punya test** dengan berkas fixture kecil. Parser adalah komponen paling berisiko di proyek ini.

### Dilarang

- Jangan memakai machine learning atau memanggil API LLM untuk analisis inti. Keluaran sistem dipakai untuk perencanaan anggaran publik, jadi harus dapat dijelaskan sepenuhnya. Ini keputusan produk, bukan keterbatasan teknis, dan harus dipertahankan saat sesi tanya jawab.
- Jangan memproses berkas XLSX di dalam siklus request HTTP.
- Jangan memakai `pd.read_excel` atau padanannya dengan pengaturan bawaan. Header berkas ini bertingkat tiga baris dengan sel ter-merge.
- Jangan menyimpan data pribadi siswa, guru, atau tenaga kependidikan. Seluruh data bersifat agregat wilayah atau satuan pendidikan.
- Jangan menambah dependensi baru tanpa alasan kuat. Setiap paket tambahan adalah risiko di lingkungan cPanel.
- Jangan menulis fitur yang tidak ada di PRD. Ruang lingkup sudah dikunci.

---

## Struktur Berkas Sumber

Detail penuh ada di `ARCHITECTURE.md` bagian 3. Ringkasnya:

**Berkas Rapor Pendidikan Indonesia** (16–21 MB, 4 edisi untuk data 2022–2025):

| Sheet | Isi |
|---|---|
| `Metadata` | 274 indikator dengan definisi dan ambang merah/kuning/hijau |
| `Nasional` | Agregat nasional |
| 38 sheet provinsi | Aceh sampai Sumatera Utara |

**Sheet provinsi** (contoh Jawa Timur: 951 baris × 548 kolom):

| Baris | Isi |
|---|---|
| 1–5 | Judul dan catatan |
| 6 | Kode indikator induk: `A.1`, `A.2` |
| 7 | Nama indikator: `A.1 Kemampuan literasi`, `A.1.1 Kompetensi membaca teks informasi` |
| 8 | Nama kolom |
| 9+ | Data |

Kolom A–D adalah dimensi: `Provinsi`, `Kabupaten/Kota`, `Jenis Satuan Pendidikan`, `Status Satuan Pendidikan`. Kolom E dan seterusnya berpasangan dua kolom per indikator: `Label Capaian {tahun}` dan `Perubahan Nilai Capaian`.

Nilai yang mungkin:
- Label: `Baik`, `Sedang`, `Kurang`, `Tidak Tersedia`
- Perubahan: `Naik`, `Turun`, `Tidak berubah`, `Tidak Tersedia`

Baris dengan `Kabupaten/Kota` bernilai `-` adalah agregat provinsi. Ini pembanding, bukan data kabupaten.

---

## Perintah Umum

```bash
# Pengembangan
php artisan serve
npm run dev

# Impor berkas di lokal (JANGAN di produksi)
php artisan akar:impor storage/app/rapor/2025_rapor_pendidikan.xlsx

# Test
php artisan test
php artisan test --filter=ParserTest

# Build aset untuk produksi
npm run build          # hasil public/build ikut di-commit

# Antrean di lokal
php artisan queue:work
```

---

## Alur Kerja

1. **Baca dulu, tulis kemudian.** Sebelum mengubah kode di `Services/Akar/`, baca berkas terkait secara utuh.
2. **Parser lebih dulu.** Tanpa parser yang bekerja, tidak ada produk. Bila diminta mengerjakan UI sementara parser belum lulus test, ingatkan.
3. **Test dengan berkas asli.** Fixture kecil untuk unit test, tetapi verifikasi akhir harus memakai berkas Rapor Pendidikan sungguhan.
4. **Commit kecil dan bermakna.** Riwayat commit ikut dinilai. Pesan commit bahasa Indonesia.
5. **Setiap anggota tim harus bisa menjelaskan kodenya sendiri.** Bila menulis bagian yang rumit, sertakan komentar yang menjelaskan alasan, bukan sekadar apa yang dilakukan.

---

## Prioritas Fitur

Kerjakan sesuai urutan. Jangan lompat.

| Kode | Fitur | Prioritas |
|---|---|---|
| F1 | Impor dan parsing berkas | P0 |
| F2 | Profil capaian daerah | P0 |
| F3 | Deteksi dan prioritisasi masalah | P0 |
| F4 | Analisis akar masalah | P0 |
| F5 | Perbandingan antardaerah | P0 |
| F7 | Generator rencana tindak lanjut | P0 |
| F8 | Ekspor PDF dan Excel | P0 |
| F6 | Analisis tren lintas tahun | P1 |
| F9 | Autentikasi dan peran | P1 |
| F10 | Mode satuan pendidikan | P1 |

**Aturan keras:** bila pada akhir 1 September parser belum berhasil membaca satu provinsi penuh, hentikan seluruh pekerjaan lain dan fokus ke parser.

Cakupan pemetaan akar masalah pada MVP adalah **15–20 indikator prioritas**, bukan seluruh 274. Untuk indikator yang belum dipetakan, tampilkan capaiannya dan nyatakan bahwa rekomendasi belum tersedia. Jangan mengarang rekomendasi.

---

## Yang Perlu Ditanyakan Lebih Dulu

- Menambah atau menghapus fitur dari daftar di atas
- Mengubah skema basis data
- Menambah dependensi Composer atau NPM
- Mengubah bobot skor prioritas atau struktur pohon keputusan
- Apa pun yang berpotensi melanggar batasan cPanel di bagian atas

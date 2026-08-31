# DESIGN.md — AKAR

Sistem desain untuk antarmuka AKAR. Arah visual: **formal pemerintahan** dengan skema **krem hangat dan biru tua**, konservatif.

---

## 1. Prinsip

**Kredibilitas di atas gaya.** Penggunanya membawa keluaran aplikasi ini ke rapat perencanaan anggaran. Tampilan harus terasa seperti dokumen resmi yang bisa dipercaya, bukan aplikasi startup.

**Krem, bukan putih.** Latar krem hangat memberi kesan dokumen cetak dan mengurangi kelelahan mata pada penggunaan lama. Putih polos membuat aplikasi terasa seperti dasbor generik. Konsekuensinya, seluruh netral juga dibuat hangat, karena abu-abu dingin akan terlihat kotor di atas krem.

**Data dulu, hiasan belakangan.** Tidak ada ilustrasi dekoratif, tidak ada gradien, tidak ada animasi yang tidak berfungsi. Setiap piksel harus membawa informasi.

**Tidak bergantung pada warna saja.** Label capaian selalu disertai teks dan ikon. Sebagian pengguna mencetak laporan hitam putih, dan sebagian mengalami buta warna.

**Ramah untuk dibaca lama.** Pengguna akan menatap tabel indikator selama puluhan menit. Kontras cukup, ukuran teks tidak kecil, baris tabel mudah dilacak.

---

## 2. Warna

Skema **krem hangat dengan biru tua**. Krem memberi kesan dokumen resmi cetak dan mengurangi kelelahan mata pada penggunaan lama. Biru tua menjaga nuansa kelembagaan.

Seluruh pasangan warna di bawah sudah diverifikasi memenuhi kontras WCAG AA minimal 4.5:1 untuk teks.

### Latar

| Token | Hex | Penggunaan | Kontras teks utama |
|---|---|---|---|
| `krem-100` | `#F5F1E6` | **Latar halaman** | 14.51:1 |
| `kartu` | `#FFFDF8` | Latar kartu, putih hangat | 16.11:1 |
| `krem-200` | `#EBE5D6` | Header tabel, latar sekunder, baris terpilih | 13.03:1 |
| `krem-300` | `#DDD5C2` | Garis pemisah, border kartu | — |

Kartu memakai putih hangat, bukan putih murni. Perbedaannya tipis tetapi mencegah kartu terasa terpotong kasar dari latar krem.

### Biru

| Token | Hex | Penggunaan | Kontras di krem |
|---|---|---|---|
| `navy-900` | `#0B2545` | Header utama, judul, sidebar | 13.63:1 |
| `biru-700` | `#14448C` | **Warna merek utama**, tombol primer, tautan | 8.32:1 |
| `biru-600` | `#1A56B0` | Hover tombol primer | — |
| `biru-100` | `#DEE7F3` | Sorotan baris, latar informasi | — |

Teks putih di atas `navy-900` mencapai 15.39:1, dan di atas `biru-700` mencapai 9.39:1.

### Netral hangat

Netral dibuat hangat agar menyatu dengan krem. Abu-abu dingin akan terlihat kotor di atas latar krem.

| Token | Hex | Penggunaan |
|---|---|---|
| `teks-900` | `#231F1A` | Teks utama |
| `teks-700` | `#4A443B` | Teks sekunder |
| `teks-500` | `#6F675B` | Teks tersier, placeholder |
| `teks-300` | `#DDD5C2` | Garis, border |

### Status capaian

Mengikuti semantik resmi Rapor Pendidikan. Jangan mengubah pemetaan ini.

| Label | Teks | Latar badge | Ikon | Kontras teks/badge |
|---|---|---|---|---|
| Baik | `#1B5E36` | `#E2ECDD` | ● | 6.40:1 |
| Sedang | `#7A4E00` | `#F5E9CE` | ◐ | 5.97:1 |
| Kurang | `#95201F` | `#F5E0DA` | ○ | 6.61:1 |
| Tidak Tersedia | `#615A4E` | `#E8E2D5` | – | 5.28:1 |

> **Aturan wajib untuk badge.** Latar badge sangat dekat dengan latar krem halaman, kontrasnya hanya sekitar 1.1:1. Karena itu setiap badge **harus memiliki border 1 px** berwarna sama dengan teksnya. Tanpa border, badge akan hilang saat diletakkan langsung di atas latar halaman.

Badge yang benar:

```
┌──────────────┐
│ ○ Kurang     │  teks #95201F, latar #F5E0DA, border 1px #95201F
└──────────────┘
```

### Arah perubahan

| Nilai | Warna | Ikon | Teks |
|---|---|---|---|
| Naik | `#1B5E36` | ▲ | Naik |
| Turun | `#95201F` | ▼ | Turun |
| Tidak berubah | `#615A4E` | ▬ | Tetap |
| Tidak Tersedia | `#615A4E` | – | Tidak tersedia |

> **Aturan wajib:** setiap penanda status memuat ikon dan teks, bukan hanya warna. Sebagian pengguna mencetak laporan hitam putih, dan sebagian mengalami buta warna.

### Aksen

| Token | Hex | Penggunaan |
|---|---|---|
| `emas-700` | `#8A6D14` | Penanda prioritas tertinggi. Dipakai sangat hemat, maksimal satu elemen per layar |

Emas bekerja baik di atas krem dan memperkuat kesan dokumen resmi. Justru karena itu mudah berlebihan. Batasi pada nomor urut prioritas pertama saja.

### Konfigurasi Tailwind

```js
// tailwind.config.js
colors: {
  krem:  { 100: '#F5F1E6', 200: '#EBE5D6', 300: '#DDD5C2' },
  kartu: '#FFFDF8',
  navy:  { 900: '#0B2545' },
  biru:  { 100: '#DEE7F3', 600: '#1A56B0', 700: '#14448C' },
  teks:  { 300: '#DDD5C2', 500: '#6F675B', 700: '#4A443B', 900: '#231F1A' },
  capaian: {
    baik:   { DEFAULT: '#1B5E36', bg: '#E2ECDD' },
    sedang: { DEFAULT: '#7A4E00', bg: '#F5E9CE' },
    kurang: { DEFAULT: '#95201F', bg: '#F5E0DA' },
    kosong: { DEFAULT: '#615A4E', bg: '#E8E2D5' },
  },
  emas: { 700: '#8A6D14' },
}
```

Jangan menambah warna di luar daftar ini.

---

## 3. Tipografi

**Huruf:** `Inter`, dengan fallback `-apple-system, "Segoe UI", Roboto, sans-serif`.
Angka pada tabel dan skor memakai `font-variant-numeric: tabular-nums` agar kolom sejajar.

| Peran | Ukuran | Tebal | Tinggi baris |
|---|---|---|---|
| Judul halaman | 24 px | 700 | 1.3 |
| Judul bagian | 18 px | 600 | 1.4 |
| Judul kartu | 15 px | 600 | 1.4 |
| Teks isi | 14 px | 400 | 1.6 |
| Teks tabel | 13 px | 400 | 1.5 |
| Label dan keterangan | 12 px | 500 | 1.4 |
| Angka skor besar | 32 px | 700 | 1.1 |

Panjang baris teks paragraf dibatasi 75 karakter. Untuk definisi indikator yang panjang, gunakan lebar maksimal `max-w-3xl`.

---

## 4. Tata Letak

### Kerangka halaman

```
┌────────────────────────────────────────────────────┐
│  HEADER  biru-900, tinggi 56px                     │
│  Logo AKAR · nama wilayah aktif · menu pengguna    │
├──────────┬─────────────────────────────────────────┤
│ SIDEBAR  │  KONTEN                                 │
│ 240px    │  padding 24px, latar krem-100           │
│ navy-900 │  lebar maksimal 1440px                  │
│          │                                         │
│ Beranda  │  ┌───────────────────────────────────┐  │
│ Profil   │  │ Kartu, latar kartu, radius 6px    │  │
│ Prioritas│  │ border krem-300, tanpa bayangan   │  │
│ Akar     │  └───────────────────────────────────┘  │
│ Banding  │                                         │
│ Tren     │                                         │
│ Rencana  │                                         │
│ Impor    │                                         │
└──────────┴─────────────────────────────────────────┘
```

Sidebar tetap terlihat, tidak dapat diciutkan pada MVP.

### Kisi

Sistem 12 kolom, jarak antarkolom 16 px. Skala jarak kelipatan 4: `4, 8, 12, 16, 24, 32, 48`.

### Responsif

Target utama layar 1280 piksel ke atas, karena pengguna bekerja di komputer kantor. Di bawah 1024 piksel, sidebar berubah menjadi menu atas dan tabel dapat digeser mendatar. Layar ponsel tidak dioptimalkan dan itu keputusan sadar, bukan kelalaian.

---

## 5. Komponen

### Kartu

Latar `kartu` (`#FFFDF8`), border 1 px `krem-300`, radius 6 px, padding 20 px. **Tanpa bayangan.** Bayangan memberi kesan aplikasi konsumen; border tegas di atas latar krem memberi kesan dokumen. Perbedaan antara putih hangat kartu dan krem halaman sudah cukup untuk memisahkan keduanya, sehingga border cukup tipis.

### Badge capaian

```
┌──────────────┐
│ ○ Kurang     │   teks 12px tebal 600, padding 4px 10px
└──────────────┘   radius 4px, warna sesuai tabel status
```

### Kartu indikator prioritas

```
┌─────────────────────────────────────────────────────┐
│ ①  A.1 Kemampuan literasi              Skor 87      │
│                                                     │
│ ○ Kurang    ▼ Turun    Peringkat 31 dari 38        │
│                                                     │
│ Berlabel Kurang dan menurun dibanding tahun lalu.   │
│ Memengaruhi 4 indikator turunan yang juga           │
│ bermasalah.                                         │
│                                                     │
│ ▸ Lihat rincian skor      ▸ Telusuri akar masalah   │
└─────────────────────────────────────────────────────┘
```

Nomor urut prioritas dalam lingkaran `biru-700`. Skor rata kanan dengan angka tabular. Kalimat penjelas selalu ada, tidak boleh kosong.

### Rincian skor

Ditampilkan sebagai bilah bertumpuk, bukan angka telanjang:

```
Label capaian      ████████████████████  40 dari 40
Arah perubahan     █████████████████     25 dari 25
Posisi relatif     ██████████            14 dari 20
Dampak turunan     ████                   8 dari 15
                                        ─────────────
                                          87 dari 100
```

Ini komponen yang akan ditunjuk juri saat bertanya soal akuntabilitas algoritma. Buat sejelas mungkin.

### Tabel indikator

- Baris tinggi 40 px, garis pemisah `krem-300` hanya mendatar
- Baris ganjil tanpa arsir, gunakan garis saja agar tidak berat
- Hover baris berlatar `krem-200`
- Header tabel lengket saat digulir, latar `krem-200`, teks 12 px tebal 600 huruf besar
- Kolom pertama lengket saat digeser mendatar
- Kolom angka rata kanan

### Pohon akar masalah

Tampilkan sebagai daftar bertingkat dengan garis penghubung, bukan diagram interaktif. Diagram memakan waktu pengembangan dan tidak menambah pemahaman di sini.

```
A.1 Kemampuan literasi  ○ Kurang
│
├─ D.1 Kualitas pembelajaran        ◐ Sedang
├─ D.2 Refleksi dan perbaikan       ○ Kurang    ← bukti
├─ D.4 Iklim keamanan               ● Baik
└─ A.3 Karakter                     ◐ Sedang

  Akar masalah terkuat
  Budaya refleksi dan perbaikan pembelajaran belum berjalan
```

Baris yang menjadi bukti diberi latar `biru-100` dan penanda teks `← bukti`.

### Tombol

| Jenis | Gaya |
|---|---|
| Primer | Latar `biru-700`, teks putih, radius 4 px, padding 8px 16px |
| Sekunder | Latar `kartu`, border `biru-700`, teks `biru-700` |
| Tersier | Teks `biru-700` tanpa latar dan border |
| Merusak | Latar `kartu`, border `capaian.kurang`, teks `capaian.kurang` |

Tinggi tombol 36 px. Tanpa sudut membulat penuh.

### Grafik

Chart.js dengan konfigurasi ketat: tanpa gradien, tanpa animasi lebih dari 200 ms, garis kisi `krem-300` tipis, sumbu berlabel jelas. Grafik tren memakai garis tunggal per indikator dengan penanda titik. Palet grafik memakai warna status, bukan warna acak.

---

## 6. Keadaan Antarmuka

Setiap halaman wajib menangani empat keadaan. Ini sering dilupakan dan langsung terlihat saat demo.

| Keadaan | Perlakuan |
|---|---|
| Memuat | Kerangka berdenyut berwarna `krem-200` pada posisi konten, bukan pemutar berputar di tengah layar |
| Kosong | Ikon garis sederhana, satu kalimat penjelas, satu tombol tindakan. Contoh: "Belum ada berkas yang diimpor. Mulai dengan mengunggah Rapor Pendidikan." |
| Galat | Kotak `capaian.kurang` dengan penjelasan spesifik. Untuk kegagalan parsing, sebutkan baris dan kolom penyebabnya |
| Data tidak tersedia | Bedakan dengan tegas dari nilai nol. Tampilkan `– Tidak tersedia` berlatar abu, bukan angka 0 |

Pembedaan keadaan terakhir penting: banyak indikator memang berlabel `Tidak Tersedia` di sumber aslinya, dan menampilkannya sebagai nol akan menyesatkan pengguna.

---

## 7. Penulisan Antarmuka

Bahasa Indonesia formal tetapi tidak kaku. Hindari istilah teknis yang tidak dipakai di lingkungan dinas dan sekolah.

| Jangan | Pakai |
|---|---|
| Upload dataset | Unggah berkas Rapor Pendidikan |
| Parsing gagal | Berkas tidak dapat dibaca |
| Score | Skor prioritas |
| Root cause | Akar masalah |
| Benchmark | Perbandingan antardaerah |
| Export | Unduh laporan |
| Dashboard | Beranda atau Ringkasan |

Pesan galat harus menyebut apa yang terjadi dan apa yang harus dilakukan. Contoh yang benar: "Berkas tidak dapat dibaca. Struktur sheet Jawa Timur tidak sesuai format Rapor Pendidikan Indonesia. Pastikan berkas diunduh langsung dari data.kemendikdasmen.go.id tanpa diubah."

---

## 8. Halaman Cetak dan PDF

Laporan yang diekspor adalah dokumen resmi yang akan diperbanyak dan dibawa ke rapat. Perlakukan sebagai keluaran utama, bukan tambahan.

- Ukuran A4, margin 2 cm
- **Latar dokumen putih murni, bukan krem.** Krem hanya untuk layar. Mencetak latar krem memboroskan tinta dan hasilnya kotor pada kertas yang sudah berwarna gading
- Kepala dokumen memuat nama wilayah, jenjang, tahun data, dan tanggal cetak
- Kaki dokumen memuat sumber data dan nomor halaman
- Warna status tetap dipakai, tetapi ikon dan teks memastikan dokumen tetap terbaca bila dicetak hitam putih
- Setiap tabel yang terpotong antarhalaman mengulang barisnya di halaman berikutnya
- Cantumkan kalimat sumber: "Sumber: Kementerian Pendidikan Dasar dan Menengah, Data Rapor Pendidikan Indonesia, diakses [tanggal]."

---

## 9. Aksesibilitas

- Kontras teks minimal 4.5:1, elemen antarmuka minimal 3:1
- Seluruh fungsi dapat dijangkau dengan papan ketik
- Penanda fokus terlihat jelas: garis luar 2 px `biru-700` dengan jarak 2 px
- Tabel memakai `<th scope>` yang benar
- Ikon status disertai teks alternatif
- Ukuran sasaran klik minimal 32 × 32 piksel

---

## 10. Yang Tidak Boleh Dilakukan

- Gradien pada latar atau tombol
- Bayangan tebal pada kartu
- Ilustrasi atau maskot
- Animasi lebih dari 200 milidetik
- Warna di luar palet yang ditetapkan
- Sudut membulat lebih dari 8 piksel
- Emoji pada antarmuka
- **Abu-abu dingin** seperti `gray-400` bawaan Tailwind. Gunakan netral hangat dari palet, karena abu-abu dingin terlihat kotor di atas krem
- **Putih murni `#FFFFFF`** sebagai latar kartu. Gunakan `kartu` (`#FFFDF8`). Putih murni hanya untuk teks di atas biru tua dan untuk latar dokumen cetak
- **Badge status tanpa border.** Latarnya terlalu dekat dengan krem halaman dan akan menghilang
- Mode gelap. Bukan prioritas, dan menambah risiko tanpa nilai tambah untuk penilaian

---

## 11. Rujukan Cepat

Salin ke `tailwind.config.js`, bagian `theme.extend.colors`. Daftar lengkap beserta rasio kontras ada di bagian 2.

```
Latar halaman     krem-100    #F5F1E6
Latar kartu       kartu       #FFFDF8
Header tabel      krem-200    #EBE5D6
Garis             krem-300    #DDD5C2
Judul & sidebar   navy-900    #0B2545
Tombol & tautan   biru-700    #14448C
Teks utama        teks-900    #231F1A
Teks sekunder     teks-700    #4A443B
Teks tersier      teks-500    #6F675B
Baik              #1B5E36 di #E2ECDD
Sedang            #7A4E00 di #F5E9CE
Kurang            #95201F di #F5E0DA
Tidak Tersedia    #615A4E di #E8E2D5
Aksen prioritas   emas-700    #8A6D14
```

Tiga aturan yang paling sering dilanggar:

1. Badge status wajib berborder 1 px sewarna teksnya
2. Setiap penanda status memuat ikon dan teks, bukan hanya warna
3. `Tidak Tersedia` bukan nilai nol, tampilkan berbeda

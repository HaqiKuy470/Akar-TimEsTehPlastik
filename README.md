# AKAR — Analisis Kausal dan Rekomendasi

Membaca berkas Rapor Pendidikan terbitan Kemendikdasmen, menemukan indikator
berlabel merah, menelusuri akar masalahnya, lalu menghasilkan draf rencana
tindak lanjut yang siap dibawa ke rapat perencanaan.

Dibangun untuk HoloDev HOLOGY 9.0 Universitas Brawijaya, subtema Pendidikan.

Dokumen rujukan: `PRD.md`, `ARCHITECTURE.md`, `DESIGN.md`, `DEPLOYMENT.md`.

---

## Menjalankan secara lokal

Prasyarat: PHP 8.3, Composer, Node.js.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
npm run build
```

Basis data mengikuti `DB_CONNECTION` di `.env`. Untuk pengembangan cepat, SQLite
sudah cukup (`DB_CONNECTION=sqlite`, `touch database/database.sqlite`). Produksi
memakai MySQL — lihat `DEPLOYMENT.md`.

### Basis data siap-demo

Satu perintah menyiapkan skema, peran, akun demo, indikator, dan satu provinsi
contoh (bila folder `dataset/` tersedia):

```bash
php artisan akar:demo --fresh
php artisan serve
```

### Mengisi data Rapor Pendidikan secara manual

Impor dilakukan di mesin lokal, bukan di server produksi (lihat
`ARCHITECTURE.md` bagian 4). Berkas Metadata indikator wajib diimpor lebih dulu.

```bash
php artisan akar:impor path/METADATA_INDIKATOR_RAPOR_PENDIDIKAN.csv
php artisan akar:impor path/data-rapor-pendidikan-indonesia.xlsx
```

---

## Akun demo

Dibuat otomatis oleh `php artisan migrate:fresh --seed`. Kata sandi ketiganya
`password`.

| Peran          | Email                   | Bisa melakukan                                      |
|----------------|-------------------------|----------------------------------------------------|
| Super Admin    | `superadmin@akar.test`  | HANYA membuat/menghapus akun; tidak melihat data apa pun |
| Administrator  | `admin@akar.test`       | Impor berkas daerah, seluruh analisis dan rencana  |
| Analis Dinas   | `analis@akar.test`      | Menjalankan analisis, menyusun rencana tindak lanjut |
| Kepala Sekolah | `kepala@akar.test`      | Mengunggah berkas satuan pendidikan, analisis, rencana |

---

## Pengujian

```bash
php artisan test
```

---

## Deployment

Target produksi adalah shared hosting **cPanel** dengan MySQL, antrean lewat
cron, dan tanpa Node/Composer di server. Langkah lengkap ada di `DEPLOYMENT.md`;
berkas pendukung ada di `deploy/` (`index.php` untuk `public_html`, `dump-db.sh`
untuk menyiapkan dump data), dan `.env.production.example` sebagai templat `.env`.

---

## Tim

Dikerjakan oleh **Tim EsTehPlastik** untuk HoloDev HOLOGY 9.0 Universitas
Brawijaya, subtema Pendidikan.

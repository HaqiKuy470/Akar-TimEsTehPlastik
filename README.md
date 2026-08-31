# AKAR — Analisis Kausal dan Rekomendasi

Membaca berkas Rapor Pendidikan terbitan Kemendikdasmen, menemukan indikator
berlabel merah, menelusuri akar masalahnya, lalu menghasilkan draf rencana
tindak lanjut yang siap dibawa ke rapat perencanaan.

Dibangun untuk HoloDev HOLOGY 9.0 Universitas Brawijaya, subtema Pendidikan.

Dokumen rujukan: `PRD.md`, `ARCHITECTURE.md`, `DESIGN.md`.

---

## Menjalankan secara lokal

Prasyarat: PHP 8.3, Composer, Node.js.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Basis data default memakai SQLite (`database/database.sqlite`); untuk produksi
cPanel gunakan MySQL, lihat `ARCHITECTURE.md`.

### Mengisi data Rapor Pendidikan

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

| Peran          | Email              | Bisa melakukan                                      |
|----------------|--------------------|----------------------------------------------------|
| Administrator  | `admin@akar.test`  | Impor berkas daerah, seluruh analisis dan rencana  |
| Analis Dinas   | `analis@akar.test` | Menjalankan analisis, menyusun rencana tindak lanjut |
| Kepala Sekolah | `kepala@akar.test` | Mengunggah berkas satuan pendidikan, analisis, rencana |

---

## Pengujian

```bash
php artisan test
```

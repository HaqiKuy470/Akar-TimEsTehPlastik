# Bundel Data Rapor Pendidikan

Data publik yang dipakai AKAR, disertakan di repositori agar aplikasi dapat
langsung dijalankan setelah clone.

**Sumber:** Portal Satu Data Kemendikdasmen — https://data.kemendikdasmen.go.id
**Tanggal pengambilan:** 30 Agustus 2026

Sitasi:

> Kementerian Pendidikan Dasar dan Menengah. (2026). *Data Rapor Pendidikan
> Indonesia*. Portal Satu Data Kemendikdasmen. https://data.kemendikdasmen.go.id

---

## Isi

### `dataset-pendidikan/01_rapor_pendidikan_indonesia/`

Empat edisi berkas Rapor Pendidikan Indonesia (data 2022–2025). Ini sumber utama
analisis AKAR. Tiap berkas memuat:

- Sheet `Metadata` — definisi 274 indikator beserta ambang merah/kuning/hijau resmi
- Sheet `Nasional` — agregat nasional
- 38 sheet provinsi (Aceh sampai Sumatera Utara)

Struktur sheet provinsi (contoh Jawa Timur: 951 baris × 548 kolom):

| Kolom | Isi |
|---|---|
| A–D | Provinsi · Kabupaten/Kota · Jenis Satuan Pendidikan · Status |
| E dst. | Berpasangan dua kolom per indikator: Label Capaian + Perubahan Nilai Capaian |

Header indikator bertingkat di baris 6–8 dengan sel ter-merge
(`A.1` → `A.1 Kemampuan literasi` → `A.1.1 Kompetensi membaca teks informasi`).
Data mulai baris 9. Rekonstruksi header ditangani `App\Services\Akar\Parsers\HeaderResolver`.

Nilai Label Capaian: `Baik`, `Sedang`, `Kurang`, `Tidak Tersedia`.
Nilai Perubahan: `Naik`, `Turun`, `Tidak berubah`, `Tidak Tersedia`.

### `METADATA_INDIKATOR_RAPOR_PENDIDIKAN.csv`

274 indikator hasil ekstraksi sheet `Metadata` ke CSV. Memuat definisi konseptual
& operasional tiap indikator, ambang batas merah/kuning/hijau resmi, dan kolom
ketersediaan indikator per tingkat (satuan / kabupaten-kota / provinsi). AKAR
membaca ambang batas dari berkas ini, bukan menetapkan kriteria sendiri.

### `dataset-pendidikan/02_indikator_prioritas/`

288 berkas indikator prioritas terpisah (2021–2025), satu berkas per indikator per
tahun. Data pelengkap; tidak semua dipakai di MVP.

### `dataset-pendidikan/03_asesmen_nasional_ringan/`

Lima berkas mikrodata Asesmen Nasional 2025 (survei kepala satuan pendidikan &
guru), sudah dianonimkan pada tingkat responden. Tiap berkas berisi sheet
`codebook_*` (kamus variabel) dan `rapor_publik` (data).

### `KATALOG_464_DATASET.csv`

Katalog dataset di portal beserta URL unduhan, pemilik data, dan jadwal
pembaruan — untuk penelusuran sumber.

---

## Catatan

Mikrodata Asesmen Nasional peserta didik (300–377 MB per berkas) **tidak**
disertakan karena ukurannya. URL-nya ada di `KATALOG_464_DATASET.csv`.

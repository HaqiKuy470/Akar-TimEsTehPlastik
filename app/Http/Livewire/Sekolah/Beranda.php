<?php

declare(strict_types=1);

namespace App\Http\Livewire\Sekolah;

use App\Services\Akar\Analysis\ProfilCapaianService;
use App\Support\SekolahPengguna;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Beranda mode satuan pendidikan.
 *
 * Halaman pertama yang dilihat kepala sekolah dan wakil kepala kurikulum
 * setelah masuk. Bila mereka belum pernah mengunggah berkas, halaman ini
 * mengajak mengunggah. Bila sudah, ia menampilkan ringkasan capaian sekolah
 * dan pintasan ke profil, prioritas, dan rencana kerja.
 *
 * Tidak ada logika analisis di sini; ringkasan diambil dari
 * ProfilCapaianService.
 */
class Beranda extends Component
{
    #[Computed]
    public function sekolah()
    {
        return app(SekolahPengguna::class)->untuk(auth()->user());
    }

    #[Computed]
    public function impor()
    {
        return app(SekolahPengguna::class)->imporTerakhir(auth()->user());
    }

    /**
     * Kombinasi jenjang/status pertama pada berkas sekolah. Berkas satuan
     * pendidikan umumnya hanya memuat satu.
     *
     * @return array{tahun: int, jenis_satuan: string, status_satuan: string}|null
     */
    #[Computed]
    public function kombinasi(): ?array
    {
        $sekolah = $this->sekolah;
        if ($sekolah === null) {
            return null;
        }

        return app(SekolahPengguna::class)->kombinasi($sekolah)[0] ?? null;
    }

    #[Computed]
    public function ringkasan(): ?array
    {
        $sekolah = $this->sekolah;
        $kombinasi = $this->kombinasi;
        if ($sekolah === null || $kombinasi === null) {
            return null;
        }

        $profil = app(ProfilCapaianService::class)->untukWilayah(
            $sekolah,
            $kombinasi['tahun'],
            $kombinasi['jenis_satuan'],
            $kombinasi['status_satuan'],
        );

        return $profil['tersedia'] ? $profil['ringkasan'] : null;
    }

    public function render()
    {
        return view('livewire.sekolah.beranda');
    }
}

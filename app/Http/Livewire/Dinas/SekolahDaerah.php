<?php

declare(strict_types=1);

namespace App\Http\Livewire\Dinas;

use App\Models\Wilayah;
use App\Services\Akar\Analysis\ProfilCapaianService;
use App\Support\SekolahPengguna;
use App\Support\SekolahWilayah;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Jembatan area dinas → area sekolah.
 *
 * Analis dinas dapat melihat capaian satu sekolah yang berkasnya sudah
 * diunggah kepala sekolahnya, sebagai konteks tambahan atas data agregat
 * kabupaten. Tampilan bersifat baca-saja; tidak ada aksi menyunting di sini,
 * dan hanya sekolah yang benar-benar sudah punya berkas yang bisa dibuka.
 *
 * Halaman ini TIDAK menggantikan area sekolah: kepala sekolah tetap bekerja
 * di ruang kerjanya sendiri. Ini hanya jendela baca dari sisi dinas.
 */
#[Title('Sekolah di wilayah')]
class SekolahDaerah extends Component
{
    /** Kabupaten/kota yang sedang ditinjau (untuk daftar sekolah). */
    #[Url]
    public ?int $kabkota = null;

    /** Sekolah yang dipilih untuk dilihat capaiannya. */
    #[Url]
    public ?int $wilayah = null;

    #[Computed]
    public function kabupaten(): ?Wilayah
    {
        return $this->kabkota !== null
            ? Wilayah::where('level', 'kabkota')->find($this->kabkota)
            : null;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    #[Computed]
    public function daftar(): Collection
    {
        if ($this->kabkota === null) {
            return collect();
        }

        return app(SekolahWilayah::class)->diKabupaten($this->kabkota);
    }

    #[Computed]
    public function sekolah(): ?Wilayah
    {
        if ($this->wilayah === null) {
            return null;
        }

        $sekolah = Wilayah::where('level', 'satuan')->find($this->wilayah);

        // Batasi ke sekolah di kabupaten yang ditinjau bila kabupaten diketahui.
        if ($sekolah !== null && $this->kabkota !== null && $sekolah->induk_id !== $this->kabkota) {
            return null;
        }

        return $sekolah;
    }

    #[Computed]
    public function profil(): ?array
    {
        $sekolah = $this->sekolah();
        if ($sekolah === null) {
            return null;
        }

        $kombinasi = app(SekolahPengguna::class)->kombinasi($sekolah);
        if ($kombinasi === []) {
            return null;
        }

        $k = $kombinasi[0];

        return app(ProfilCapaianService::class)
            ->untukWilayah($sekolah, $k['tahun'], $k['jenis_satuan'], $k['status_satuan']);
    }

    public function render()
    {
        return view('livewire.dinas.sekolah-daerah')
            ->layout('layouts::app', ['header' => 'Sekolah di wilayah']);
    }
}

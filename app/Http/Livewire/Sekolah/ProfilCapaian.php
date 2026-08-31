<?php

declare(strict_types=1);

namespace App\Http\Livewire\Sekolah;

use App\Services\Akar\Analysis\ProfilCapaianService;
use App\Support\SekolahPengguna;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Profil capaian satu sekolah (mode satuan pendidikan).
 *
 * Berbeda dari versi dinas, komponen ini tidak punya pemilih wilayah:
 * wilayahnya sudah tetap, yaitu sekolah milik pengguna. Pemilih jenjang dan
 * status hanya muncul bila berkas sekolah memuat lebih dari satu kombinasi.
 * Seluruh perhitungan tetap di ProfilCapaianService.
 */
class ProfilCapaian extends Component
{
    #[Url]
    public string $jenisSatuan = '';

    #[Url]
    public string $statusSatuan = '';

    public function mount(): void
    {
        $pertama = $this->kombinasiTersedia()->first();
        if ($pertama !== null) {
            $this->jenisSatuan = $this->jenisSatuan ?: $pertama['jenis_satuan'];
            $this->statusSatuan = $this->statusSatuan ?: $pertama['status_satuan'];
        }
    }

    #[Computed]
    public function sekolah()
    {
        return app(SekolahPengguna::class)->untuk(auth()->user());
    }

    /**
     * @return Collection<int, array{tahun: int, jenis_satuan: string, status_satuan: string}>
     */
    #[Computed]
    public function kombinasiTersedia(): Collection
    {
        $sekolah = $this->sekolah;
        if ($sekolah === null) {
            return collect();
        }

        return collect(app(SekolahPengguna::class)->kombinasi($sekolah));
    }

    /** @return Collection<int, string> */
    #[Computed]
    public function jenjangTersedia(): Collection
    {
        return $this->kombinasiTersedia()->pluck('jenis_satuan')->unique()->values();
    }

    /** @return Collection<int, string> */
    #[Computed]
    public function statusTersedia(): Collection
    {
        return $this->kombinasiTersedia()
            ->where('jenis_satuan', $this->jenisSatuan)
            ->pluck('status_satuan')->unique()->values();
    }

    #[Computed]
    public function tahun(): ?int
    {
        return $this->kombinasiTersedia()
            ->firstWhere('jenis_satuan', $this->jenisSatuan)['tahun'] ?? null;
    }

    #[Computed]
    public function profil(): ?array
    {
        $sekolah = $this->sekolah;
        $tahun = $this->tahun;

        if ($sekolah === null || $tahun === null || $this->jenisSatuan === '' || $this->statusSatuan === '') {
            return null;
        }

        return app(ProfilCapaianService::class)
            ->untukWilayah($sekolah, $tahun, $this->jenisSatuan, $this->statusSatuan);
    }

    public function render()
    {
        return view('livewire.sekolah.profil-capaian');
    }
}

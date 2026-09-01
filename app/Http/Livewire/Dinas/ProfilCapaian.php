<?php

declare(strict_types=1);

namespace App\Http\Livewire\Dinas;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Wilayah;
use App\Services\Akar\Analysis\ProfilCapaianService;
use App\Support\SekolahWilayah;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * F2 — Profil Capaian Daerah.
 *
 * Komponen ini hanya mengurus pilihan pengguna dan menampilkan hasil. Seluruh
 * perhitungan ada di ProfilCapaianService, sesuai aturan pemisahan logika di
 * CLAUDE.md.
 */
class ProfilCapaian extends Component
{
    #[Url]
    public ?int $tahun = null;

    #[Url]
    public string $provinsi = '';

    #[Url]
    public ?int $wilayahId = null;

    #[Url]
    public string $jenisSatuan = '';

    #[Url]
    public string $statusSatuan = '';

    public function mount(): void
    {
        $this->tahun ??= $this->tahunTersedia()->first();
    }

    /**
     * Saat provinsi berganti, pilihan kabupaten/kota lama tidak lagi sah.
     */
    public function updatedProvinsi(): void
    {
        $this->wilayahId = null;
    }

    public function updatedTahun(): void
    {
        $this->jenisSatuan = '';
        $this->statusSatuan = '';
    }

    /**
     * @return Collection<int, int>
     */
    #[Computed]
    public function tahunTersedia(): Collection
    {
        return ImporBerkas::query()
            ->where('jenis', 'daerah')
            ->where('status', 'selesai')
            ->whereNotNull('tahun_edisi')
            ->orderByDesc('tahun_edisi')
            ->distinct()
            ->pluck('tahun_edisi');
    }

    /**
     * @return Collection<int, string>
     */
    #[Computed]
    public function provinsiTersedia(): Collection
    {
        return Wilayah::query()
            ->where('level', 'kabkota')
            ->whereNotNull('provinsi')
            ->orderBy('provinsi')
            ->distinct()
            ->pluck('provinsi');
    }

    /**
     * @return Collection<int, Wilayah>
     */
    #[Computed]
    public function kabkotaTersedia(): Collection
    {
        if ($this->provinsi === '') {
            return collect();
        }

        return Wilayah::query()
            ->where('level', 'kabkota')
            ->where('provinsi', $this->provinsi)
            ->orderBy('kabupaten_kota')
            ->get(['id', 'kabupaten_kota']);
    }

    /**
     * @return Collection<int, string>
     */
    #[Computed]
    public function jenisSatuanTersedia(): Collection
    {
        if ($this->tahun === null) {
            return collect();
        }

        return Capaian::query()
            ->where('tahun', $this->tahun)
            ->orderBy('jenis_satuan')
            ->distinct()
            ->pluck('jenis_satuan');
    }

    /**
     * @return Collection<int, string>
     */
    #[Computed]
    public function statusSatuanTersedia(): Collection
    {
        if ($this->tahun === null || $this->jenisSatuan === '') {
            return collect();
        }

        return Capaian::query()
            ->where('tahun', $this->tahun)
            ->where('jenis_satuan', $this->jenisSatuan)
            ->orderBy('status_satuan')
            ->distinct()
            ->pluck('status_satuan');
    }

    #[Computed]
    public function profil(): ?array
    {
        if ($this->tahun === null || $this->wilayahId === null || $this->jenisSatuan === '' || $this->statusSatuan === '') {
            return null;
        }

        $wilayah = Wilayah::find($this->wilayahId);
        if ($wilayah === null) {
            return null;
        }

        return app(ProfilCapaianService::class)
            ->untukWilayah($wilayah, $this->tahun, $this->jenisSatuan, $this->statusSatuan);
    }

    /**
     * Sekolah di kabupaten/kota terpilih yang berkas Rapor Pendidikan
     * satuannya sudah diunggah kepala sekolahnya. Menautkan area dinas ke
     * area sekolah tanpa menggabungkannya.
     *
     * @return Collection<int, array<string, mixed>>
     */
    #[Computed]
    public function sekolahDiWilayah(): Collection
    {
        if ($this->wilayahId === null) {
            return collect();
        }

        $wilayah = Wilayah::find($this->wilayahId);
        if ($wilayah === null || $wilayah->level !== 'kabkota') {
            return collect();
        }

        return app(SekolahWilayah::class)->diKabupaten($this->wilayahId);
    }

    public function render()
    {
        return view('livewire.dinas.profil-capaian')
            ->layout('layouts::app', ['header' => 'Profil capaian daerah']);
    }
}

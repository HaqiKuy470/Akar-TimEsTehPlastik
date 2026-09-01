<?php

declare(strict_types=1);

namespace App\Http\Livewire\Dinas;

use App\Models\Capaian;
use App\Models\Wilayah;
use App\Services\Akar\Analysis\TrenService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Tren extends Component
{
    #[Url]
    public string $provinsi = '';

    #[Url]
    public ?int $wilayahId = null;

    #[Url]
    public string $jenisSatuan = '';

    #[Url]
    public string $statusSatuan = '';

    public function updatedProvinsi(): void
    {
        $this->wilayahId = null;
    }

    /** @return Collection<int, string> */
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

    /** @return Collection<int, Wilayah> */
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

    /** @return Collection<int, string> */
    #[Computed]
    public function jenisSatuanTersedia(): Collection
    {
        return Capaian::query()->orderBy('jenis_satuan')->distinct()->pluck('jenis_satuan');
    }

    /** @return Collection<int, string> */
    #[Computed]
    public function statusSatuanTersedia(): Collection
    {
        if ($this->jenisSatuan === '') {
            return collect();
        }

        return Capaian::query()
            ->where('jenis_satuan', $this->jenisSatuan)
            ->orderBy('status_satuan')
            ->distinct()
            ->pluck('status_satuan');
    }

    /** @return array<string, mixed>|null */
    #[Computed]
    public function tren(): ?array
    {
        if ($this->wilayahId === null || $this->jenisSatuan === '' || $this->statusSatuan === '') {
            return null;
        }

        $wilayah = Wilayah::find($this->wilayahId);
        if ($wilayah === null) {
            return null;
        }

        return app(TrenService::class)->untukWilayah($wilayah, $this->jenisSatuan, $this->statusSatuan);
    }

    public function render()
    {
        return view('livewire.dinas.tren')
            ->layout('layouts::app', ['header' => 'Tren lintas tahun']);
    }
}

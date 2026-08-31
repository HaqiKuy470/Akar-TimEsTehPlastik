<?php

declare(strict_types=1);

namespace App\Http\Livewire\Dinas;

use App\Models\ImporBerkas as ImporBerkasModel;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * F1 — Riwayat & pemantauan impor berkas.
 *
 * Berkas daerah berukuran 16-21 MB tidak diunggah lewat halaman ini: parsing
 * dilakukan di mesin lokal dengan `php artisan akar:impor <berkas> --async`
 * (ARCHITECTURE.md bagian 4.1). Halaman ini menampilkan riwayat dan status
 * setiap impor, termasuk yang masih dalam antrean.
 */
class ImporBerkas extends Component
{
    /**
     * @return Collection<int, ImporBerkasModel>
     */
    #[Computed]
    public function riwayat(): Collection
    {
        return ImporBerkasModel::query()
            ->latest('id')
            ->get();
    }

    #[Computed]
    public function adaYangDiproses(): bool
    {
        return $this->riwayat()->contains(
            fn (ImporBerkasModel $b) => in_array($b->status, ['antre', 'proses'], true)
        );
    }

    public function render()
    {
        return view('livewire.dinas.impor-berkas')
            ->layout('layouts::app', ['header' => 'Impor berkas']);
    }
}

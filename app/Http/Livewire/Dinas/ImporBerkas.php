<?php

declare(strict_types=1);

namespace App\Http\Livewire\Dinas;

use App\Models\ImporBerkas as ImporBerkasModel;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ImporBerkas extends Component
{
    /** @return Collection<int, ImporBerkasModel> */
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

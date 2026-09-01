<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman panduan penggunaan AKAR.
 *
 * Seluruh penjelasan cara memakai aplikasi, arti label capaian, cara skor
 * prioritas dihitung, dan batasan produk dikumpulkan di satu tempat. Halaman
 * alat (Profil, Prioritas, dan seterusnya) cukup memuat tautan ke bagian yang
 * relevan di sini, sehingga tetap ringkas untuk pengguna yang sudah paham.
 *
 * Tidak ada logika analisis di sini; bobot skor dibaca dari config/akar.php
 * hanya untuk ditampilkan apa adanya.
 */
#[Title('Panduan')]
class Panduan extends Component
{
    public function render()
    {
        return view('livewire.panduan', [
            'bobot' => (array) config('akar.bobot_komponen', []),
        ])->layout('layouts.app', ['header' => 'Panduan penggunaan']);
    }
}

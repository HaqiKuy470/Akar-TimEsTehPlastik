<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;

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

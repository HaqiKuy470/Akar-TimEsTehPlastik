<?php

use App\Http\Livewire\Dinas\Perbandingan;
use App\Http\Livewire\Dinas\Prioritas;
use App\Http\Livewire\Dinas\ProfilCapaian;
use App\Http\Livewire\Dinas\RencanaTindakLanjut;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dinas/profil');

Route::prefix('dinas')->name('dinas.')->group(function () {
    Route::get('profil', ProfilCapaian::class)->name('profil');
    Route::get('prioritas', Prioritas::class)->name('prioritas');
    Route::get('banding', Perbandingan::class)->name('banding');
    Route::get('rencana', RencanaTindakLanjut::class)->name('rencana');
});

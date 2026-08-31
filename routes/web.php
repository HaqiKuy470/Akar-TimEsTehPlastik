<?php

use App\Http\Livewire\Dinas\ProfilCapaian;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dinas/profil');

Route::prefix('dinas')->name('dinas.')->group(function () {
    Route::get('profil', ProfilCapaian::class)->name('profil');
});

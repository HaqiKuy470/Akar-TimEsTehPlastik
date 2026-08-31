<?php

use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Dinas\Perbandingan;
use App\Http\Livewire\Dinas\Prioritas;
use App\Http\Livewire\Dinas\ProfilCapaian;
use App\Http\Livewire\Dinas\RencanaTindakLanjut;
use App\Http\Livewire\Dinas\Tren;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(Auth::check() ? 'dinas.profil' : 'login'));

Route::middleware('guest')->group(function () {
    Route::get('login', Login::class)->name('login');
});

Route::post('logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

/*
| Halaman analisis level daerah. Seluruh peran yang sudah masuk boleh melihat
| analisis; pembatasan per aksi (mis. impor berkas daerah hanya admin)
| ditegakkan di komponen masing-masing lewat izin Spatie.
*/
Route::prefix('dinas')->name('dinas.')->middleware('auth')->group(function () {
    Route::get('profil', ProfilCapaian::class)->name('profil');
    Route::get('prioritas', Prioritas::class)->name('prioritas');
    Route::get('banding', Perbandingan::class)->name('banding');
    Route::get('tren', Tren::class)->name('tren');
    Route::get('rencana', RencanaTindakLanjut::class)->name('rencana');
});

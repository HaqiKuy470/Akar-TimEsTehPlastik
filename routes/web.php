<?php

use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Dinas\ImporBerkas;
use App\Http\Livewire\Dinas\Perbandingan;
use App\Http\Livewire\Dinas\Prioritas;
use App\Http\Livewire\Dinas\ProfilCapaian;
use App\Http\Livewire\Dinas\RencanaTindakLanjut;
use App\Http\Livewire\Dinas\SekolahDaerah;
use App\Http\Livewire\Dinas\Tren;
use App\Http\Livewire\Panduan;
use App\Http\Livewire\Sekolah\Beranda as SekolahBeranda;
use App\Http\Livewire\Sekolah\Prioritas as SekolahPrioritas;
use App\Http\Livewire\Sekolah\ProfilCapaian as SekolahProfilCapaian;
use App\Http\Livewire\Sekolah\RencanaKerja;
use App\Http\Livewire\Sekolah\UnggahBerkas;
use App\Http\Middleware\AreaPeran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return redirect()->route(
        Auth::user()->hasRole('kepala_sekolah') ? 'sekolah.beranda' : 'dinas.profil'
    );
});

Route::middleware('guest')->group(function () {
    Route::get('login', Login::class)->name('login');
});

Route::post('logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

// Panduan penggunaan — dapat diakses semua peran yang sudah masuk, di luar
// pembatasan area, karena penjelasannya berlaku untuk kedua mode.
Route::get('panduan', Panduan::class)->middleware('auth')->name('panduan');

/*
| Analisis level daerah — untuk admin dan analis dinas. Kepala sekolah yang
| membukanya diarahkan ke berandanya sendiri (AreaPeran).
*/
Route::prefix('dinas')->name('dinas.')->middleware(['auth', AreaPeran::class.':dinas'])->group(function () {
    Route::get('profil', ProfilCapaian::class)->name('profil');
    Route::get('prioritas', Prioritas::class)->name('prioritas');
    Route::get('banding', Perbandingan::class)->name('banding');
    Route::get('tren', Tren::class)->name('tren');
    Route::get('rencana', RencanaTindakLanjut::class)->name('rencana');
    Route::get('impor', ImporBerkas::class)->name('impor');

    // Jendela baca ke area sekolah: capaian satu sekolah yang berkasnya
    // sudah diunggah, sebagai konteks atas data agregat kabupaten.
    Route::get('sekolah', SekolahDaerah::class)->name('sekolah');
});

/*
| Mode satuan pendidikan — untuk kepala sekolah (dan admin). Berkas Rapor
| Pendidikan sekolah diunggah sendiri; logika analisisnya identik dengan
| level daerah, hanya pembandingnya agregat kabupaten dan provinsi.
*/
Route::prefix('sekolah')->name('sekolah.')->middleware('auth')->group(function () {
    Route::get('beranda', SekolahBeranda::class)->name('beranda');
    Route::get('unggah', UnggahBerkas::class)->name('unggah');
    Route::get('profil', SekolahProfilCapaian::class)->name('profil');
    Route::get('prioritas', SekolahPrioritas::class)->name('prioritas');
    Route::get('rkt', RencanaKerja::class)->name('rkt');
});

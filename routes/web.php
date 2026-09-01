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
use App\Http\Livewire\Superadmin\KelolaAkun;
use App\Http\Middleware\AreaPeran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $pengguna = Auth::user();

    return redirect()->route(match (true) {
        $pengguna->hasRole('superadmin') => 'akun',
        $pengguna->hasRole('kepala_sekolah') => 'sekolah.beranda',
        default => 'dinas.profil',
    });
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

// Panduan penggunaan — untuk semua peran yang bekerja dengan data (dinas &
// sekolah). Superadmin diarahkan kembali ke halaman kelola akun.
Route::get('panduan', Panduan::class)->middleware(['auth', AreaPeran::class.':umum'])->name('panduan');

// Area superadmin — HANYA mengelola akun, tanpa akses ke data analisis.
Route::get('akun', KelolaAkun::class)->middleware(['auth', AreaPeran::class.':akun'])->name('akun');

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
Route::prefix('sekolah')->name('sekolah.')->middleware(['auth', AreaPeran::class.':sekolah'])->group(function () {
    Route::get('beranda', SekolahBeranda::class)->name('beranda');
    Route::get('unggah', UnggahBerkas::class)->name('unggah');
    Route::get('profil', SekolahProfilCapaian::class)->name('profil');
    Route::get('prioritas', SekolahPrioritas::class)->name('prioritas');
    Route::get('rkt', RencanaKerja::class)->name('rkt');
});

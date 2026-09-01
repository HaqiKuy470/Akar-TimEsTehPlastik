<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memisahkan area aplikasi (akun / dinas / sekolah) menurut peran. Pengguna di
 * luar wewenangnya dialihkan ke berandanya sendiri, bukan ditolak dengan galat.
 * Area sekolah tidak dibatasi peran karena datanya sudah terisolasi per
 * pengunggah (SekolahPengguna).
 *
 * Dipakai lewat nama kelas langsung di routes/web.php
 * (`AreaPeran::class.':dinas'`), tanpa alias di bootstrap/app.php.
 */
class AreaPeran
{
    public function handle(Request $request, Closure $next, string $area): Response
    {
        $pengguna = $request->user();

        if ($pengguna === null) {
            return $next($request);
        }

        $superadmin = $pengguna->hasRole('superadmin');
        $kepalaSekolah = $pengguna->hasRole('kepala_sekolah');

        if ($superadmin && $area !== 'akun') {
            return redirect()->route('akun');
        }

        if (! $superadmin && $area === 'akun') {
            return redirect()->route(
                $kepalaSekolah ? 'sekolah.beranda' : 'dinas.profil'
            );
        }

        if ($area === 'dinas' && $kepalaSekolah) {
            return redirect()->route('sekolah.beranda');
        }

        return $next($request);
    }
}

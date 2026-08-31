<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memisahкан dua area aplikasi menurut peran pengguna.
 *
 *  - Area "dinas": analisis level daerah, untuk admin dan analis dinas.
 *  - Area "sekolah": analisis satuan pendidikan, ruang kerja kepala sekolah.
 *
 * Kepala sekolah yang membuka area dinas diarahкан ke berandanya sendiri,
 * bukan ditolak dengan galat. Area sekolah tidak dibatasi peran: datanya
 * sudah terisolasi per pengunggah (SekolahPengguna menyaring berdasarkan
 * impor_berkas.diunggah_oleh), jadi pengguna dinas yang membukanya hanya
 * melihat ajakan mengunggah, bukan data sekolah orang lain.
 *
 * Dipakai lewat nama kelas langsung di routes/web.php
 * (`AreaPeran::class.':dinas'`), tanpa perlu alias di bootstrap/app.php.
 */
class AreaPeran
{
    public function handle(Request $request, Closure $next, string $area): Response
    {
        $pengguna = $request->user();

        if ($area === 'dinas' && $pengguna !== null && $pengguna->hasRole('kepala_sekolah')) {
            return redirect()->route('sekolah.beranda');
        }

        return $next($request);
    }
}

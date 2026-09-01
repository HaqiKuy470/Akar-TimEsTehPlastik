<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memisahkan area aplikasi menurut peran pengguna, dan menegakkan
 * pemisahan tugas.
 *
 *  - Area "akun"    : hanya superadmin. Membuat/menghapus akun, tanpa akses
 *                     ke data analisis apa pun.
 *  - Area "dinas"   : analisis level daerah, untuk admin dan analis dinas.
 *  - Area "sekolah" : analisis satuan pendidikan, ruang kerja kepala sekolah.
 *
 * Pengguna yang membuka area di luar wewenangnya diarahkan ke berandanya
 * sendiri, bukan ditolak dengan galat. Data sekolah tetap terisolasi per
 * pengunggah (SekolahPengguna), jadi area sekolah tidak dibatasi peran.
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

        // Superadmin hanya boleh di area akun.
        if ($superadmin && $area !== 'akun') {
            return redirect()->route('akun');
        }

        // Selain superadmin tidak boleh masuk area akun.
        if (! $superadmin && $area === 'akun') {
            return redirect()->route(
                $kepalaSekolah ? 'sekolah.beranda' : 'dinas.profil'
            );
        }

        // Kepala sekolah yang membuka area dinas diarahkan ke berandanya.
        if ($area === 'dinas' && $kepalaSekolah) {
            return redirect()->route('sekolah.beranda');
        }

        return $next($request);
    }
}

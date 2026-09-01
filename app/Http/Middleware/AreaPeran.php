<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

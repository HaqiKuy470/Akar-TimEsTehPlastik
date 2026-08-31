<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($header ?? null) ? $header.' — AKAR' : 'AKAR — Analisis Kausal dan Rekomendasi' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;450;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full">
@php
    $kepalaSekolah = auth()->check() && auth()->user()->hasRole('kepala_sekolah');

    // [route, label, ikon]. Ikon = garis tunggal 1.5px, bukan set generik.
    $ikon = [
        'profil' => '<path d="M4 5h16M4 12h16M4 19h9" stroke-width="1.5" stroke-linecap="round"/>',
        'prioritas' => '<path d="M12 3l2.4 5 5.6.7-4 3.9 1 5.5L12 15.9 6.9 21l1-5.5-4-3.9L9.6 8 12 3z" stroke-width="1.5" stroke-linejoin="round"/>',
        'banding' => '<path d="M6 20V9M12 20V4M18 20v-7" stroke-width="1.5" stroke-linecap="round"/>',
        'tren' => '<path d="M4 16l5-5 4 3 7-8M20 6h-4M20 6v4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
        'rencana' => '<path d="M7 3h7l4 4v14H7V3z" stroke-width="1.5" stroke-linejoin="round"/><path d="M9 12h6M9 16h6" stroke-width="1.5" stroke-linecap="round"/>',
        'impor' => '<path d="M12 15V4m0 11l-3-3m3 3l3-3M5 20h14" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
        'beranda' => '<path d="M4 11l8-6 8 6v9H4v-9z" stroke-width="1.5" stroke-linejoin="round"/>',
        'unggah' => '<path d="M12 4v11m0-11L9 7m3-3l3 3M5 20h14" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
    ];

    $menu = $kepalaSekolah ? [
        ['sekolah.beranda', 'Beranda', 'beranda'],
        ['sekolah.profil', 'Profil capaian', 'profil'],
        ['sekolah.prioritas', 'Prioritas & akar masalah', 'prioritas'],
        ['sekolah.rkt', 'Rencana Kerja Tahunan', 'rencana'],
        ['sekolah.unggah', 'Unggah berkas', 'unggah'],
    ] : [
        ['dinas.profil', 'Profil capaian', 'profil'],
        ['dinas.prioritas', 'Prioritas & akar masalah', 'prioritas'],
        ['dinas.banding', 'Perbandingan antardaerah', 'banding'],
        ['dinas.tren', 'Tren lintas tahun', 'tren'],
        ['dinas.rencana', 'Rencana tindak lanjut', 'rencana'],
        ['dinas.impor', 'Impor berkas', 'impor'],
        ['sekolah.unggah', 'Mode satuan pendidikan', 'unggah'],
    ];

    $labelPeran = ['admin' => 'Administrator', 'analis_dinas' => 'Analis Dinas', 'kepala_sekolah' => 'Kepala Sekolah'];
    $peran = auth()->check() ? auth()->user()->getRoleNames()->first() : null;
@endphp

    <div class="flex min-h-full">
        <aside class="hidden w-[248px] shrink-0 flex-col bg-navy-900 text-white lg:flex">
            <div class="flex h-14 items-center gap-2 border-b border-white/10 px-5">
                <span class="text-[15px] font-bold tracking-[0.08em]">AKAR</span>
                <span class="text-[11px] text-biru-300">Rapor Pendidikan</span>
            </div>

            <nav class="flex flex-1 flex-col gap-0.5 px-3 py-4">
                <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.12em] text-white/35">
                    {{ $kepalaSekolah ? 'Satuan pendidikan' : 'Analisis daerah' }}
                </p>
                @foreach ($menu as [$nama, $label, $kunciIkon])
                    @continue(! \Illuminate\Support\Facades\Route::has($nama))
                    @php $aktif = request()->routeIs($nama); @endphp
                    <a href="{{ route($nama) }}" wire:navigate
                       @class([
                           'group relative flex items-center gap-3 rounded-md py-2 pl-3 pr-2.5 text-[13px]',
                           'bg-white/10 font-medium text-white' => $aktif,
                           'text-white/60 hover:bg-white/[0.06] hover:text-white/90' => ! $aktif,
                       ])>
                        @if ($aktif)
                            <span class="absolute inset-y-1.5 left-0 w-0.5 rounded-full bg-biru-300"></span>
                        @endif
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"
                             class="size-4 shrink-0 {{ $aktif ? 'text-biru-300' : 'text-white/40 group-hover:text-white/70' }}">
                            {!! $ikon[$kunciIkon] ?? '' !!}
                        </svg>
                        <span class="truncate">{{ $label }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 px-5 py-4 text-[11px] text-white/35">
                Sumber: Kemendikdasmen
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex h-14 items-center justify-between border-b border-krem-300 bg-navy-900 px-6 text-white">
                <div class="flex items-center gap-2 text-[13px] font-semibold">
                    <span class="lg:hidden">AKAR</span>
                    <span class="hidden lg:inline">{{ $header ?? 'Beranda' }}</span>
                </div>
                @auth
                    <div class="flex items-center gap-3">
                        <span class="hidden text-[12px] text-white/60 sm:inline">
                            {{ auth()->user()->name }}
                            @if ($peran)<span class="text-white/30"> · </span>{{ $labelPeran[$peran] ?? $peran }}@endif
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-md border border-white/25 px-2.5 py-1 text-[12px] font-medium text-white/80 hover:border-white/50 hover:bg-white/10 hover:text-white active:translate-y-px">
                                Keluar
                            </button>
                        </form>
                    </div>
                @endauth
            </header>

            <main class="mx-auto w-full max-w-[1400px] flex-1 px-6 pb-16 pt-7">
                {{ $slot }}
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>

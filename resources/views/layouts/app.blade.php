<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AKAR — Analisis Kausal dan Rekomendasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full">
    <div class="flex min-h-full">
        {{-- Sidebar tetap, tidak dapat diciutkan pada MVP (DESIGN.md 4). --}}
        <aside class="hidden w-60 shrink-0 bg-navy-900 text-white lg:block">
            <div class="flex h-14 items-center px-5 text-lg font-bold tracking-wide">AKAR</div>
            <nav class="mt-2 flex flex-col gap-0.5 px-2 text-[13px]">
                @php
                    // Hanya menu yang rutenya sudah terdaftar. "Akar masalah"
                    // adalah penelusuran di dalam halaman Prioritas (DESIGN.md 5).
                    $menu = [
                        ['dinas.profil', 'Profil capaian'],
                        ['dinas.prioritas', 'Prioritas & akar masalah'],
                        ['dinas.banding', 'Perbandingan antardaerah'],
                        ['dinas.tren', 'Tren lintas tahun'],
                        ['dinas.rencana', 'Rencana tindak lanjut'],
                        ['dinas.impor', 'Impor berkas'],
                        ['sekolah.unggah', 'Mode satuan pendidikan'],
                    ];
                @endphp
                @foreach ($menu as [$nama, $label])
                    @continue(! \Illuminate\Support\Facades\Route::has($nama))
                    <a href="{{ route($nama) }}"
                       @class([
                           'rounded px-3 py-2',
                           'bg-white/10 font-semibold text-white' => request()->routeIs($nama),
                           'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs($nama),
                       ])>{{ $label }}</a>
                @endforeach
            </nav>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-14 items-center justify-between border-b border-krem-300 bg-navy-900 px-6 text-white">
                <span class="text-sm font-semibold">{{ $header ?? 'Rapor Pendidikan' }}</span>
                @auth
                    @php
                        $labelPeran = [
                            'admin' => 'Administrator',
                            'analis_dinas' => 'Analis Dinas',
                            'kepala_sekolah' => 'Kepala Sekolah',
                        ];
                        $peran = auth()->user()->getRoleNames()->first();
                    @endphp
                    <div class="flex items-center gap-3 text-[13px]">
                        <span class="text-white/70">
                            {{ auth()->user()->name }}
                            @if ($peran)
                                <span class="text-white/40">·</span> {{ $labelPeran[$peran] ?? $peran }}
                            @endif
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded border border-white/30 px-2.5 py-1 text-white/80 hover:bg-white/10 hover:text-white">
                                Keluar
                            </button>
                        </form>
                    </div>
                @endauth
            </header>

            <main class="mx-auto w-full max-w-[1440px] flex-1 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>

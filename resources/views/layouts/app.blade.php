<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'AKAR' }} — AKAR</title>
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
                    $menu = [
                        ['profil', 'Profil capaian'],
                        ['prioritas', 'Prioritas masalah'],
                        ['akar', 'Akar masalah'],
                        ['banding', 'Perbandingan'],
                        ['tren', 'Tren'],
                        ['rencana', 'Rencana tindak lanjut'],
                        ['impor', 'Impor berkas'],
                    ];
                @endphp
                @foreach ($menu as [$key, $label])
                    @php $aktif = request()->routeIs("dinas.$key"); @endphp
                    <a href="{{ \Illuminate\Support\Facades\Route::has("dinas.$key") ? route("dinas.$key") : '#' }}"
                       @class([
                           'rounded px-3 py-2',
                           'bg-white/10 font-semibold text-white' => $aktif,
                           'text-white/70 hover:bg-white/5 hover:text-white' => ! $aktif,
                       ])>{{ $label }}</a>
                @endforeach
            </nav>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-14 items-center justify-between border-b border-krem-300 bg-navy-900 px-6 text-white">
                <span class="text-sm font-semibold">{{ $header ?? 'Rapor Pendidikan' }}</span>
                <span class="text-[13px] text-white/70">Dinas Pendidikan</span>
            </header>

            <main class="mx-auto w-full max-w-[1440px] flex-1 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>

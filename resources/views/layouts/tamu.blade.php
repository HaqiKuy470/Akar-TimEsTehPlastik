<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Masuk' }} — AKAR</title>
    <link rel="icon" href="{{ asset('logo.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,400;1,9..144,500&family=Inter:wght@400;450;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="grid min-h-full lg:grid-cols-[1fr_460px]">
    <aside class="relative hidden flex-col justify-between bg-navy-900 p-12 text-white lg:flex">
        <div class="flex items-center gap-3.5">
            <img src="{{ asset('logo.svg') }}" alt="" class="size-11 shrink-0">
            <span class="merek text-[25px]" style="color: var(--color-krem-merek)">AKAR</span>
            <span class="h-6 w-px bg-white/20" aria-hidden="true"></span>
            <span class="merek-tagline text-[15px] text-biru-300">Analisis Kausal dan Rekomendasi</span>
        </div>

        <div class="max-w-md">
            <p class="text-[22px] font-semibold leading-snug">
                Rapor Pendidikan memberi tahu bahwa nilainya merah.
                AKAR memberi tahu apa yang harus dilakukan Senin pagi.
            </p>
            <p class="mt-4 text-[13px] leading-relaxed text-white/60">
                Satu logika analisis, dua level pengguna: dinas melihat seluruh
                kabupatennya, kepala sekolah melihat sekolahnya sendiri.
            </p>
        </div>

        <div class="space-y-1 text-[11px] text-white/35">
            <p>
                Sumber data: Kementerian Pendidikan Dasar dan Menengah,
                Portal Satu Data Kemendikdasmen.
            </p>
            <p>Dibuat oleh Tim EsTehPlastik.</p>
        </div>
    </aside>
    <main class="flex items-center justify-center bg-krem-100 p-6">
        <div class="w-full max-w-sm">
            <div class="mb-7 flex items-center gap-2.5 lg:hidden">
                <img src="{{ asset('logo.svg') }}" alt="" class="size-10">
                <div>
                    <div class="merek text-[22px] text-navy-900">AKAR</div>
                    <p class="merek-tagline text-[12px] text-teks-500">Analisis Kausal dan Rekomendasi</p>
                </div>
            </div>

            {{ $slot }}
        </div>
    </main>
    @livewireScripts
</body>
</html>

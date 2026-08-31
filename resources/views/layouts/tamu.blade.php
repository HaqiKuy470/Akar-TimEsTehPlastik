<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Masuk' }} — AKAR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-full items-center justify-center bg-krem-100 p-6">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center">
            <div class="text-2xl font-bold tracking-wide text-navy-900">AKAR</div>
            <p class="mt-1 text-[13px] text-teks-500">Analisis Kausal dan Rekomendasi</p>
        </div>

        <div class="rounded-md border border-krem-300 bg-kartu p-6">
            {{ $slot }}
        </div>

        <p class="mt-4 text-center text-xs text-teks-500">
            Rapor Pendidikan menjadi rencana tindak lanjut yang siap dibawa ke rapat.
        </p>
    </div>
    @livewireScripts
</body>
</html>

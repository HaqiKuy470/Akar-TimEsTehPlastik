<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('kode') — AKAR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>
<body class="flex min-h-full items-center justify-center bg-krem-100 p-6">
    <div class="w-full max-w-md rounded-[--radius-kartu] border border-krem-300 bg-kartu p-8 text-center">
        <div class="tabular text-[64px] font-bold leading-none text-navy-900">@yield('kode')</div>
        <h1 class="mt-4 text-[17px] font-semibold text-teks-900">@yield('judul')</h1>
        <p class="mx-auto mt-1.5 max-w-xs text-[13px] text-teks-500">@yield('pesan')</p>
        <div class="mt-6">
            <x-tombol jenis="primer" href="/">Kembali ke beranda</x-tombol>
        </div>
    </div>
</body>
</html>

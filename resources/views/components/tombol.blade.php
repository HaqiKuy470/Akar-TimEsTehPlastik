@props([
    'jenis' => 'primer',   // primer | sekunder | tersier | merusak
    'ukuran' => 'sedang',   // sedang | kecil
    'href' => null,
])

@php
    $dasar = 'inline-flex items-center justify-center gap-1.5 rounded-md font-semibold whitespace-nowrap select-none '
        .'active:translate-y-px disabled:pointer-events-none disabled:opacity-45';

    $gaya = match ($jenis) {
        'primer' => 'bg-biru-700 text-white hover:bg-biru-600',
        'sekunder' => 'border border-biru-700 bg-kartu text-biru-700 hover:bg-biru-100',
        'merusak' => 'border border-kurang bg-kartu text-kurang hover:bg-kurang-bg',
        default => 'text-biru-700 hover:text-biru-600 hover:underline underline-offset-2',
    };

    $ukuranKelas = $ukuran === 'kecil'
        ? 'h-8 px-3 text-[12px]'
        : 'h-9 px-4 text-[13px]';

    if ($jenis === 'tersier') {
        $ukuranKelas = $ukuran === 'kecil' ? 'text-[12px]' : 'text-[13px]';
    }

    $kelas = trim("$dasar $gaya $ukuranKelas");
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $kelas]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $kelas]) }}>{{ $slot }}</button>
@endif

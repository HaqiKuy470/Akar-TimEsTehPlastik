@props(['judul', 'pesan' => null, 'ikon' => 'dokumen'])

@php
    // Ikon garis sederhana (DESIGN.md 6), satu ketebalan garis.
    $path = match ($ikon) {
        'unggah' => '<path d="M12 16V6m0 0l-3.5 3.5M12 6l3.5 3.5M6 18h12" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
        'grafik' => '<path d="M5 19V9m5 10V5m5 14v-7m5 7V8M4 21h16" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
        'filter' => '<path d="M4 6h16M7 12h10M10 18h4" stroke-width="1.5" stroke-linecap="round"/>',
        default => '<path d="M7 4h7l4 4v12H7V4z" stroke-width="1.5" stroke-linejoin="round"/><path d="M14 4v4h4" stroke-width="1.5" stroke-linejoin="round"/>',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-3 px-6 py-14 text-center']) }}>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-9 text-teks-400" aria-hidden="true">
        {!! $path !!}
    </svg>
    <div>
        <p class="text-[15px] font-semibold text-teks-900">{{ $judul }}</p>
        @if ($pesan)
            <p class="mx-auto mt-1 max-w-md text-[13px] text-teks-500">{{ $pesan }}</p>
        @endif
    </div>
    @isset($aksi)
        <div class="mt-1">{{ $aksi }}</div>
    @endisset
</div>

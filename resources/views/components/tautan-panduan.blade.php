@props([
    'anchor' => null,   // id bagian di halaman panduan, mis. "prioritas"
    'teks' => 'Cara membaca halaman ini',
])

@php
    $tujuan = route('panduan').($anchor ? '#'.$anchor : '');
@endphp
<a href="{{ $tujuan }}"
   {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-md border border-krem-300 bg-kartu px-2.5 py-1 text-[12px] font-medium text-teks-700 hover:border-biru-700 hover:text-biru-700']) }}>
    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" aria-hidden="true" class="size-3.5">
        <circle cx="8" cy="8" r="6.25" stroke-width="1.3"/>
        <path d="M6.4 6.2a1.7 1.7 0 013.05 1c-.35.55-1.2.85-1.45 1.3-.1.2-.1.4-.1.7" stroke-width="1.3" stroke-linecap="round"/>
        <circle cx="8" cy="11.4" r="0.55" fill="currentColor" stroke="none"/>
    </svg>
    {{ $teks }}
</a>

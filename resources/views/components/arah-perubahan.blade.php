@props(['nilai'])

@php
    // DESIGN.md 2: setiap penanda status memuat ikon DAN teks, bukan warna saja.
    $peta = [
        'Naik' => ['teks' => 'text-baik', 'ikon' => '▲', 'label' => 'Naik'],
        'Turun' => ['teks' => 'text-kurang', 'ikon' => '▼', 'label' => 'Turun'],
        'Tidak berubah' => ['teks' => 'text-kosong', 'ikon' => '▬', 'label' => 'Tetap'],
    ];
    $g = $peta[$nilai] ?? ['teks' => 'text-kosong', 'ikon' => '–', 'label' => 'Tidak tersedia'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 text-[11px] font-medium leading-none $g[teks]"]) }}>
    <span aria-hidden="true" class="relative -top-px text-[8px] leading-none">{{ $g['ikon'] }}</span>
    <span>{{ $g['label'] }}</span>
</span>

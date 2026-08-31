@props(['nilai'])

@php
    // DESIGN.md 2: ikon + teks, bukan hanya warna.
    $peta = [
        'Naik' => ['teks' => 'text-baik', 'ikon' => '▲', 'label' => 'Naik'],
        'Turun' => ['teks' => 'text-kurang', 'ikon' => '▼', 'label' => 'Turun'],
        'Tidak berubah' => ['teks' => 'text-kosong', 'ikon' => '▬', 'label' => 'Tetap'],
    ];
    $g = $peta[$nilai] ?? ['teks' => 'text-kosong', 'ikon' => '–', 'label' => 'Tidak tersedia'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 text-xs font-medium $g[teks]"]) }}>
    <span aria-hidden="true">{{ $g['ikon'] }}</span>{{ $g['label'] }}
</span>

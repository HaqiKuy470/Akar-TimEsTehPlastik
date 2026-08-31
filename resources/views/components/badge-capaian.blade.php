@props(['label'])

@php
    // DESIGN.md 2: setiap badge WAJIB berborder 1px sewarna teksnya, karena
    // latar badge terlalu dekat dengan latar krem halaman. Penanda selalu
    // memuat ikon dan teks, bukan hanya warna.
    $peta = [
        'Baik' => ['teks' => 'text-baik', 'bg' => 'bg-baik-bg', 'border' => 'border-baik', 'ikon' => '●'],
        'Sedang' => ['teks' => 'text-sedang', 'bg' => 'bg-sedang-bg', 'border' => 'border-sedang', 'ikon' => '◐'],
        'Kurang' => ['teks' => 'text-kurang', 'bg' => 'bg-kurang-bg', 'border' => 'border-kurang', 'ikon' => '○'],
    ];
    $g = $peta[$label] ?? ['teks' => 'text-kosong', 'bg' => 'bg-kosong-bg', 'border' => 'border-kosong', 'ikon' => '–'];
    $teksTampil = $label === 'Tidak Tersedia' ? 'Tidak tersedia' : $label;
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded border px-2.5 py-1 text-xs font-semibold $g[teks] $g[bg] $g[border]"]) }}>
    <span aria-hidden="true">{{ $g['ikon'] }}</span>{{ $teksTampil }}
</span>

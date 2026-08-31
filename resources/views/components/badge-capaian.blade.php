@props(['label'])

@php
    // DESIGN.md 2 (aturan wajib): setiap badge HARUS berborder 1px sewarna
    // teksnya — latar badge terlalu dekat dengan latar krem halaman dan akan
    // hilang tanpa border. Penanda selalu memuat ikon DAN teks, bukan warna
    // saja, agar terbaca saat dicetak hitam-putih atau oleh pengguna buta warna.
    $peta = [
        'Baik' => ['teks' => 'text-baik', 'bg' => 'bg-baik-bg', 'border' => 'border-baik', 'ikon' => '●'],
        'Sedang' => ['teks' => 'text-sedang', 'bg' => 'bg-sedang-bg', 'border' => 'border-sedang', 'ikon' => '◐'],
        'Kurang' => ['teks' => 'text-kurang', 'bg' => 'bg-kurang-bg', 'border' => 'border-kurang', 'ikon' => '○'],
    ];
    $g = $peta[$label] ?? ['teks' => 'text-kosong', 'bg' => 'bg-kosong-bg', 'border' => 'border-kosong', 'ikon' => '–'];
    $teksTampil = $label === 'Tidak Tersedia' ? 'Tidak tersedia' : $label;
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded border px-2 py-0.5 text-[11px] font-semibold leading-none $g[teks] $g[bg] $g[border]"]) }}>
    <span aria-hidden="true" class="relative -top-px text-[9px] leading-none">{{ $g['ikon'] }}</span>
    <span>{{ $teksTampil }}</span>
</span>

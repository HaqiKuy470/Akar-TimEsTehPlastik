@props(['item' => []])

@php
    // 'warna' salah satu: kurang | sedang | baik | kosong | biru
    $garis = [
        'kurang' => 'bg-kurang',
        'sedang' => 'bg-sedang',
        'baik' => 'bg-baik',
        'kosong' => 'bg-kosong',
        'biru' => 'bg-biru-700',
    ];
    $angka = [
        'kurang' => 'text-kurang',
        'sedang' => 'text-sedang',
        'baik' => 'text-baik',
        'kosong' => 'text-kosong',
        'biru' => 'text-navy-900',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'grid overflow-hidden rounded-[--radius-kartu] border border-krem-300 bg-kartu']) }}
     style="grid-template-columns: repeat({{ max(count($item), 1) }}, minmax(0, 1fr));">
    @foreach ($item as $i => $satu)
        @php $w = $satu['warna'] ?? 'biru'; @endphp
        <div class="relative px-5 py-4 {{ $i > 0 ? 'border-l border-krem-300' : '' }}">
            <span class="absolute inset-x-0 top-0 h-0.5 {{ $garis[$w] ?? $garis['biru'] }}"></span>
            <div class="tabular text-[28px] font-bold leading-none tracking-tight {{ $angka[$w] ?? $angka['biru'] }}">{{ $satu['angka'] }}</div>
            <div class="mt-1.5 text-[12px] font-medium text-teks-700">{{ $satu['label'] }}</div>
            @if (! empty($satu['sub']))
                <div class="text-[11px] text-teks-400">{{ $satu['sub'] }}</div>
            @endif
        </div>
    @endforeach
</div>

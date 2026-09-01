@props([
    'dimensi' => [],   // list of {kode, nama, hitung:{merah,kuning,hijau,kosong}}
    'judul' => 'Komposisi capaian per dimensi',
])

@php
    // Bentuk data siap-pakai untuk Chart.js. Dilakukan di Blade, bukan di JS,
    // agar komponen Livewire tetap tipis.
    $seri = [
        ['kunci' => 'merah', 'label' => 'Kurang', 'warna' => '--color-grafik-kurang'],
        ['kunci' => 'kuning', 'label' => 'Sedang', 'warna' => '--color-grafik-sedang'],
        ['kunci' => 'hijau', 'label' => 'Baik', 'warna' => '--color-grafik-baik'],
        ['kunci' => 'kosong', 'label' => 'Tidak tersedia', 'warna' => '--color-grafik-kosong'],
    ];

    $data = [
        'label_baris' => array_map(fn ($d) => $d['kode'].'. '.$d['nama'], $dimensi),
        'seri' => array_map(fn ($s) => [
            'label' => $s['label'],
            'warna' => $s['warna'],
            'nilai' => array_map(fn ($d) => $d['hitung'][$s['kunci']] ?? 0, $dimensi),
        ], $seri),
    ];
@endphp

@if (! empty($dimensi))
    <div wire:ignore
         x-data="grafikKomposisi(@js($data))"
         x-init="gambar()"
         {{ $attributes->merge(['class' => 'flex flex-col gap-3']) }}>
        <div class="flex items-center gap-3">
            <h3 class="text-[13px] font-semibold uppercase tracking-[0.05em] text-teks-700">{{ $judul }}</h3>
            <span class="h-px flex-1 bg-krem-300"></span>
        </div>

        {{-- Legenda: ikon + teks, tidak mengandalkan warna saja (DESIGN.md 2). --}}
        <div class="flex flex-wrap gap-x-5 gap-y-1.5 text-[12px] text-teks-700">
            <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-[2px]" style="background:var(--color-grafik-kurang)"></span>Kurang</span>
            <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-[2px]" style="background:var(--color-grafik-sedang)"></span>Sedang</span>
            <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-[2px]" style="background:var(--color-grafik-baik)"></span>Baik</span>
            <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-[2px]" style="background:var(--color-grafik-kosong)"></span>Tidak tersedia</span>
        </div>

        <div class="w-full overflow-x-auto">
            <div style="height: {{ max(count($dimensi) * 46 + 24, 120) }}px; min-width: 420px;">
                <canvas x-ref="kanvas"></canvas>
            </div>
        </div>
    </div>
@endif

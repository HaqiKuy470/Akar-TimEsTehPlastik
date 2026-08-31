@props(['komponen' => [], 'skor' => 0])

@php
    // DESIGN.md 5 "Rincian skor": bilah bertumpuk, bukan angka telanjang.
    // Komponen inilah yang akan ditunjuk juri saat bertanya soal akuntabilitas
    // algoritma, jadi tiap komponen skor dipecah jelas: kontribusi vs bobot maksimum.
    $totalMaks = collect($komponen)->sum(fn ($k) => (float) ($k['bobot_maks'] ?? 0)) ?: 100;
@endphp

<div {{ $attributes->merge(['class' => 'rounded border border-krem-300 bg-krem-100 p-4']) }}>
    <div class="flex flex-col gap-2.5">
        @foreach ($komponen as $k)
            @php
                $maks = (float) ($k['bobot_maks'] ?? 0);
                $kontribusi = (float) ($k['kontribusi'] ?? 0);
                $persen = $maks > 0 ? max(0, min(100, $kontribusi / $maks * 100)) : 0;
            @endphp
            <div class="grid grid-cols-[9rem_1fr_5.5rem] items-center gap-3 text-[13px]">
                <span class="text-teks-700">{{ $k['nama'] ?? $k['kode'] ?? '—' }}</span>
                <span class="h-3 overflow-hidden rounded-sm bg-krem-300">
                    <span class="block h-full bg-biru-700" style="width: {{ $persen }}%"></span>
                </span>
                <span class="tabular text-right text-teks-900">
                    {{ rtrim(rtrim(number_format($kontribusi, 1, ',', '.'), '0'), ',') }}
                    <span class="text-teks-500">dari {{ (int) $maks }}</span>
                </span>
            </div>
        @endforeach
    </div>

    <div class="mt-3 grid grid-cols-[9rem_1fr_5.5rem] gap-3 border-t border-krem-300 pt-2.5 text-[13px]">
        <span class="font-semibold text-teks-900">Skor prioritas</span>
        <span></span>
        <span class="tabular text-right font-bold text-teks-900">
            {{ rtrim(rtrim(number_format((float) $skor, 1, ',', '.'), '0'), ',') }}
            <span class="font-normal text-teks-500">dari {{ (int) $totalMaks }}</span>
        </span>
    </div>
</div>

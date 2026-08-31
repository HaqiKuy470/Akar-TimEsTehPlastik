@props(['judul', 'jumlah' => null])

<div {{ $attributes->merge(['class' => 'flex items-baseline gap-3']) }}>
    <h2 class="text-[13px] font-semibold uppercase tracking-[0.05em] text-teks-700">{{ $judul }}</h2>
    @if (! is_null($jumlah))
        <span class="tabular text-[12px] text-teks-400">{{ $jumlah }}</span>
    @endif
    <span class="h-px flex-1 bg-krem-300"></span>
</div>

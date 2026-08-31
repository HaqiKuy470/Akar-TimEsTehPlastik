@props(['judul', 'lead' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-2']) }}>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <h1 class="text-[22px] font-bold leading-tight text-teks-900">{{ $judul }}</h1>
        @isset($aksi)
            <div class="shrink-0">{{ $aksi }}</div>
        @endisset
    </div>

    @if ($lead)
        <p class="max-w-2xl text-[13.5px] leading-relaxed text-teks-700">{{ $lead }}</p>
    @endif

    @isset($konteks)
        <p class="text-[12px] text-teks-500">{{ $konteks }}</p>
    @endisset
</div>

@props(['judul' => null, 'sub' => null, 'rapat' => false])

{{-- Kartu: batas tegas di atas latar krem, tanpa bayangan (DESIGN.md 5). --}}
<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-[--radius-kartu] border border-krem-300 bg-kartu']) }}>
    @if ($judul || isset($aksi))
        <header class="flex items-start justify-between gap-4 border-b border-krem-300 px-5 py-3.5">
            <div class="min-w-0">
                @if ($judul)
                    <h2 class="text-[15px] font-semibold text-teks-900">{{ $judul }}</h2>
                @endif
                @if ($sub)
                    <p class="mt-0.5 text-[12px] text-teks-500">{{ $sub }}</p>
                @endif
            </div>
            @isset($aksi)
                <div class="shrink-0">{{ $aksi }}</div>
            @endisset
        </header>
    @endif

    <div class="{{ $rapat ? '' : 'p-5' }}">
        {{ $slot }}
    </div>
</section>

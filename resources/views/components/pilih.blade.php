@props(['label' => null, 'hint' => null])

{{-- Pemilih bergaya konsisten dengan sistem. Tetap memakai <select> asli
     agar perilaku wire:model, papan ketik, dan layar kecil tidak berubah. --}}
<label {{ $attributes->only('class')->merge(['class' => 'flex min-w-0 flex-col gap-1.5']) }}>
    @if ($label)
        <span class="text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">{{ $label }}</span>
    @endif

    <div class="relative">
        <select {{ $attributes->except('class')->merge(['class' => 'peer h-9 w-full appearance-none rounded-md border border-krem-300 bg-kartu pl-3 pr-9 text-[13px] text-teks-900 hover:border-teks-400 focus:border-biru-700 disabled:cursor-not-allowed disabled:border-krem-300 disabled:bg-krem-150 disabled:text-teks-400']) }}>
            {{ $slot }}
        </select>
        <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"
             class="pointer-events-none absolute right-3 top-1/2 size-3.5 -translate-y-1/2 text-teks-400 peer-focus:text-biru-700 peer-disabled:opacity-40">
            <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>

    @if ($hint)
        <span class="text-[11px] text-teks-400">{{ $hint }}</span>
    @endif
</label>

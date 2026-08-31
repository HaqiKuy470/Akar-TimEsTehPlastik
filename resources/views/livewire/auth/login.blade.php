<div class="rounded-[--radius-kartu] border border-krem-300 bg-kartu p-7">
    <h1 class="text-[19px] font-semibold text-teks-900">Masuk</h1>
    <p class="mt-1 text-[13px] text-teks-500">Gunakan akun yang diberikan dinas atau sekolah Anda.</p>

    <form wire:submit="login" class="mt-6 flex flex-col gap-4">
        <label class="flex flex-col gap-1.5">
            <span class="text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Email</span>
            <input type="email" wire:model="email" autocomplete="username" autofocus
                   class="h-9 rounded-md border border-krem-300 bg-kartu px-3 text-[13px] text-teks-900 hover:border-teks-400 focus:border-biru-700">
            @error('email')
                <span class="text-[12px] text-kurang">{{ $message }}</span>
            @enderror
        </label>

        <label class="flex flex-col gap-1.5">
            <span class="text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Kata sandi</span>
            <input type="password" wire:model="password" autocomplete="current-password"
                   class="h-9 rounded-md border border-krem-300 bg-kartu px-3 text-[13px] text-teks-900 hover:border-teks-400 focus:border-biru-700">
            @error('password')
                <span class="text-[12px] text-kurang">{{ $message }}</span>
            @enderror
        </label>

        <label class="flex items-center gap-2 text-[13px] text-teks-700">
            <input type="checkbox" wire:model="ingatSaya" class="size-3.5 rounded border-krem-300 text-biru-700 focus:ring-biru-700">
            Ingat saya di perangkat ini
        </label>

        <button type="submit"
                class="mt-1 h-9 rounded-md bg-biru-700 px-4 text-[13px] font-semibold text-white hover:bg-biru-600 active:translate-y-px">
            <span wire:loading.remove wire:target="login">Masuk</span>
            <span wire:loading wire:target="login">Memeriksa…</span>
        </button>
    </form>
</div>

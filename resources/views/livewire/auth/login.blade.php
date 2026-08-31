<div>
    <h1 class="text-[18px] font-semibold text-teks-900">Masuk</h1>
    <p class="mt-1 text-[13px] text-teks-500">Gunakan akun yang diberikan dinas atau sekolah Anda.</p>

    <form wire:submit="login" class="mt-5 flex flex-col gap-4">
        <label class="flex flex-col gap-1 text-xs font-medium text-teks-700">
            Email
            <input type="email" wire:model="email" autocomplete="username" autofocus
                   class="h-9 rounded border border-krem-300 bg-white px-3 text-[13px] text-teks-900">
            @error('email')
                <span class="text-xs font-normal text-kurang">{{ $message }}</span>
            @enderror
        </label>

        <label class="flex flex-col gap-1 text-xs font-medium text-teks-700">
            Kata sandi
            <input type="password" wire:model="password" autocomplete="current-password"
                   class="h-9 rounded border border-krem-300 bg-white px-3 text-[13px] text-teks-900">
            @error('password')
                <span class="text-xs font-normal text-kurang">{{ $message }}</span>
            @enderror
        </label>

        <label class="flex items-center gap-2 text-[13px] text-teks-700">
            <input type="checkbox" wire:model="ingatSaya" class="rounded border-krem-300">
            Ingat saya di perangkat ini
        </label>

        <button type="submit"
                class="h-9 rounded bg-biru-700 px-4 text-[13px] font-semibold text-white hover:bg-biru-600">
            <span wire:loading.remove wire:target="login">Masuk</span>
            <span wire:loading wire:target="login">Memeriksa…</span>
        </button>
    </form>
</div>

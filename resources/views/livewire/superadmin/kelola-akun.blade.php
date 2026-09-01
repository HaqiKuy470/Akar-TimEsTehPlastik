<div class="flex flex-col gap-6">
    <x-kepala-halaman
        judul="Kelola akun"
        lead="Buat akun untuk dinas pendidikan atau kepala sekolah. Halaman ini tidak menampilkan data Rapor Pendidikan; hanya nama, email, dan peran tiap akun." />

    @if (session('sukses'))
        <div class="rounded-md border border-baik bg-baik-bg px-4 py-3 text-[13px] text-baik">
            {{ session('sukses') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
        {{-- Formulir buat akun --}}
        <x-kartu judul="Buat akun baru">
            <form wire:submit="buatAkun" class="flex flex-col gap-4">
                <label class="flex flex-col gap-1.5">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Nama</span>
                    <input type="text" wire:model="nama"
                           class="h-9 rounded-md border border-krem-300 bg-kartu px-3 text-[13px] text-teks-900 hover:border-teks-400 focus:border-biru-700">
                    @error('nama') <span class="text-[12px] text-kurang">{{ $message }}</span> @enderror
                </label>

                <label class="flex flex-col gap-1.5">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Email</span>
                    <input type="email" wire:model="email" autocomplete="off"
                           class="h-9 rounded-md border border-krem-300 bg-kartu px-3 text-[13px] text-teks-900 hover:border-teks-400 focus:border-biru-700">
                    @error('email') <span class="text-[12px] text-kurang">{{ $message }}</span> @enderror
                </label>

                <x-pilih label="Peran" wire:model="peran">
                    @foreach (\App\Http\Livewire\Superadmin\KelolaAkun::PERAN_DIIZINKAN as $kode => $label)
                        <option value="{{ $kode }}">{{ $label }}</option>
                    @endforeach
                </x-pilih>
                @error('peran') <span class="-mt-2 text-[12px] text-kurang">{{ $message }}</span> @enderror

                <label class="flex flex-col gap-1.5">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Kata sandi awal</span>
                    <input type="text" wire:model="kataSandi" autocomplete="off"
                           class="h-9 rounded-md border border-krem-300 bg-kartu px-3 text-[13px] text-teks-900 hover:border-teks-400 focus:border-biru-700">
                    @error('kataSandi') <span class="text-[12px] text-kurang">{{ $message }}</span> @enderror
                    <span class="text-[11px] text-teks-400">Sampaikan kata sandi ini ke pengguna. Minimal 8 karakter.</span>
                </label>

                <x-tombol type="submit" class="mt-1">
                    <span wire:loading.remove wire:target="buatAkun">Buat akun</span>
                    <span wire:loading wire:target="buatAkun">Menyimpan…</span>
                </x-tombol>
            </form>
        </x-kartu>

        {{-- Daftar akun --}}
        <div class="flex flex-col gap-3">
            <x-judul-bagian judul="Akun terdaftar" :jumlah="$daftar->count().' akun'" />

            <x-kartu rapat>
                <div class="overflow-x-auto">
                    <table class="w-full text-[13px]">
                        <thead>
                            <tr class="border-b border-krem-300 text-left">
                                <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Nama</th>
                                <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Email</th>
                                <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Peran</th>
                                <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Dibuat</th>
                                <th class="px-4 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daftar as $u)
                                @php $peranU = $u->roles->pluck('name')->first(); @endphp
                                <tr class="border-b border-krem-300 last:border-0 hover:bg-krem-150">
                                    <td class="px-4 py-3 font-medium text-teks-900">{{ $u->name }}</td>
                                    <td class="px-4 py-3 text-teks-700">{{ $u->email }}</td>
                                    <td class="px-4 py-3 text-teks-700">{{ $labelPeran[$peranU] ?? ($peranU ?? '—') }}</td>
                                    <td class="px-4 py-3 tabular text-teks-500">{{ $u->created_at?->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @if ($u->id === auth()->id())
                                            <span class="text-[11px] text-teks-400">Anda</span>
                                        @elseif ($peranU === 'superadmin')
                                            <span class="text-[11px] text-teks-400">—</span>
                                        @elseif ($hapusId === $u->id)
                                            <span class="inline-flex items-center gap-2">
                                                <x-tombol jenis="merusak" ukuran="kecil" wire:click="hapusAkun">Hapus</x-tombol>
                                                <x-tombol jenis="tersier" ukuran="kecil" wire:click="batalHapus">Batal</x-tombol>
                                            </span>
                                        @else
                                            <x-tombol jenis="tersier" ukuran="kecil" wire:click="konfirmasiHapus({{ $u->id }})">Hapus</x-tombol>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-kartu>

            <p class="text-[11px] text-teks-400">
                Akun super admin tidak dapat dihapus dari sini, dan Anda tidak dapat menghapus akun Anda sendiri.
            </p>
        </div>
    </div>
</div>

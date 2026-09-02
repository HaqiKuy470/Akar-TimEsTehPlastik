<div class="flex flex-col gap-0 overflow-hidden">
    <section class="border-y border-krem-300 px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="grid gap-10 lg:grid-cols-[1.45fr_.55fr] lg:items-end">
            <div>
                <div class="flex max-w-lg items-center gap-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-navy-900">
                    <span>AKAR / RENCANA TINDAK LANJUT</span>
                    <span class="h-px flex-1 bg-krem-300"></span>
                </div>
                <h1 class="mt-6 max-w-[10ch] text-[clamp(3rem,7vw,7rem)] font-semibold leading-[.9] tracking-[-.06em] text-navy-900">
                    Dari temuan,
                    <span class="block font-normal text-teks-400">jadi tindakan.</span>
                </h1>
                <p class="mt-6 max-w-2xl text-[15px] leading-7 text-teks-700">
                    Susun draf pembenahan dari hasil analisis akar masalah, lalu sesuaikan kegiatan,
                    penanggung jawab, indikator keberhasilan, dan waktunya sebelum digunakan.
                </p>
            </div>

            <aside class="border-t border-krem-300 pt-5 lg:border-l lg:border-t-0 lg:pl-7 lg:pt-0">
                <div class="flex items-center justify-between gap-4">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.12em] text-teks-500">Workspace rencana</span>
                    <x-tautan-panduan anchor="rencana" />
                </div>
                <div class="mt-8 border-t border-krem-300 pt-4">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.1em] text-teks-400">Status</p>
                            <p class="mt-1 text-[18px] font-semibold text-teks-900">
                                @if ($rencanaId)
                                    Draf tersedia
                                @elseif ($this->analisis !== null)
                                    Siap disusun
                                @else
                                    Pilih analisis
                                @endif
                            </p>
                        </div>
                        @if ($rencanaId)
                            <div class="text-right">
                                <span class="tabular text-[36px] font-semibold leading-none tracking-tight text-navy-900">{{ count($item) }}</span>
                                <p class="mt-1 text-[11px] text-teks-500">butir rencana</p>
                            </div>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <section class="bg-kartu px-4 py-7 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[3.5rem_1fr_.65fr] lg:items-start">
            <span class="text-[11px] font-semibold tracking-[0.12em] text-teks-400">01</span>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-teks-500">Sumber rencana</p>
                <h2 class="mt-2 max-w-xl text-[clamp(1.7rem,3vw,3.2rem)] font-medium leading-[1.02] tracking-[-.045em] text-navy-900">
                    Pilih analisis yang ingin ditindaklanjuti.
                </h2>
            </div>
            <p class="text-[12px] leading-6 text-teks-500 lg:text-right">
                Draf dibuat dari hasil prioritas dan akar masalah yang sudah dianalisis sebelumnya.
            </p>
        </div>

        <div class="mt-6 border-y border-krem-300 py-4">
            <label class="block max-w-3xl">
                <span class="mb-2 block text-[10px] font-semibold uppercase tracking-[0.12em] text-teks-500">Analisis</span>
                <select wire:model.live="analisisId" class="w-full border-0 bg-transparent p-0 pr-8 text-[15px] font-semibold text-teks-900 outline-none focus:ring-0">
                    <option value="">— pilih analisis —</option>
                    @foreach ($this->analisisTersedia as $opsi)
                        <option value="{{ $opsi['id'] }}">{{ $opsi['label'] }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        @if ($this->analisisTersedia->isEmpty())
            <p class="mt-4 text-[13px] text-teks-500">
                Belum ada analisis. Buka
                <a href="{{ route('dinas.prioritas') }}" wire:navigate class="font-medium text-biru-700 underline underline-offset-2">Prioritas &amp; akar masalah</a>
                dan jalankan analisis untuk sebuah wilayah lebih dulu.
            </p>
        @endif

        @if ($this->analisis !== null)
            <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-krem-300 pt-4">
                <x-tombol wire:click="susunDraf">{{ $rencanaId ? 'Susun ulang draf' : 'Susun draf' }}</x-tombol>
                @if ($rencanaId)
                    <x-tombol jenis="sekunder" wire:click="tambahBaris">Tambah baris</x-tombol>
                    <x-tombol wire:click="simpan">Simpan</x-tombol>
                @endif
                @if ($tersimpan)
                    <span class="ml-1 text-[12px] font-semibold text-baik">✓ Tersimpan</span>
                @endif
                @if ($rencanaId)
                    <div class="ml-auto flex items-center gap-2">
                        <span class="text-[10px] font-semibold uppercase tracking-[0.1em] text-teks-500">Unduh</span>
                        <x-tombol jenis="sekunder" ukuran="kecil" wire:click="unduhPdf">PDF</x-tombol>
                        <x-tombol jenis="sekunder" ukuran="kecil" wire:click="unduhExcel">Excel</x-tombol>
                    </div>
                @endif
            </div>
        @endif
    </section>

    @if ($this->analisis !== null)
        <section class="border-t border-krem-300 px-4 py-8 sm:px-6 lg:px-8">
            @if ($rencanaId === null)
                <div class="grid min-h-64 gap-6 py-10 lg:grid-cols-[3.5rem_1fr]">
                    <span class="text-[11px] font-semibold tracking-[0.12em] text-teks-400">02</span>
                    <div class="max-w-2xl">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-teks-500">Belum ada draf</p>
                        <h2 class="mt-3 text-[clamp(2rem,4vw,4rem)] font-medium leading-[1] tracking-[-.05em] text-navy-900">Analisis sudah siap. Tinggal ubah temuan menjadi rencana.</h2>
                        <p class="mt-4 max-w-xl text-[13px] leading-6 text-teks-500">Tekan “Susun draf” untuk menghasilkan rencana awal dari hasil analisis akar masalah.</p>
                    </div>
                </div>
            @elseif (empty($item))
                <div class="grid min-h-64 gap-6 py-10 lg:grid-cols-[3.5rem_1fr]">
                    <span class="text-[11px] font-semibold tracking-[0.12em] text-teks-400">02</span>
                    <div class="max-w-2xl">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-teks-500">Draf kosong</p>
                        <h2 class="mt-3 text-[clamp(2rem,4vw,4rem)] font-medium leading-[1] tracking-[-.05em] text-navy-900">Belum ada akar masalah yang cukup didukung bukti.</h2>
                        <p class="mt-4 text-[13px] leading-6 text-teks-500">Anda tetap dapat menambahkan butir rencana secara manual melalui tombol “Tambah baris”.</p>
                    </div>
                </div>
            @else
                <div class="mb-6 grid gap-6 lg:grid-cols-[3.5rem_1fr_.6fr]">
                    <span class="text-[11px] font-semibold tracking-[0.12em] text-teks-400">02</span>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-teks-500">Draf rencana</p>
                        <h2 class="mt-2 text-[clamp(1.8rem,3.2vw,3.4rem)] font-medium leading-[1.02] tracking-[-.045em] text-navy-900">Ubah setiap temuan menjadi langkah yang bisa dikerjakan.</h2>
                    </div>
                    <p class="text-[12px] leading-6 text-teks-500 lg:text-right">Semua perubahan baru tersimpan setelah tombol “Simpan” ditekan.</p>
                </div>

                <div class="border-t border-krem-300">
                    @foreach ($item as $i => $baris)
                        <article wire:key="baris-{{ $i }}" class="grid gap-5 border-b border-krem-300 py-6 lg:grid-cols-[4rem_1fr]">
                            <div class="flex items-start justify-between lg:block">
                                <span class="tabular text-[28px] font-medium leading-none tracking-tight text-teks-400">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="lg:mt-6"><x-tombol jenis="merusak" ukuran="kecil" wire:click="hapusBaris({{ $i }})">Hapus</x-tombol></div>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                                <label class="block">
                                    <span class="mb-2 block text-[10px] font-semibold uppercase tracking-[0.1em] text-teks-500">Masalah</span>
                                    <textarea rows="4" wire:model="item.{{ $i }}.masalah" class="w-full resize-y rounded-md border border-krem-300 bg-kartu p-3 text-[13px] leading-6 text-teks-900 hover:border-teks-400 focus:border-biru-700 focus:ring-0">{{ $baris['masalah'] ?? '' }}</textarea>
                                </label>
                                <label class="block">
                                    <span class="mb-2 block text-[10px] font-semibold uppercase tracking-[0.1em] text-teks-500">Akar masalah</span>
                                    <textarea rows="4" wire:model="item.{{ $i }}.akar_masalah" class="w-full resize-y rounded-md border border-krem-300 bg-kartu p-3 text-[13px] leading-6 text-teks-900 hover:border-teks-400 focus:border-biru-700 focus:ring-0">{{ $baris['akar_masalah'] ?? '' }}</textarea>
                                </label>
                                <label class="block">
                                    <span class="mb-2 block text-[10px] font-semibold uppercase tracking-[0.1em] text-teks-500">Kegiatan</span>
                                    <textarea rows="4" wire:model="item.{{ $i }}.kegiatan" class="w-full resize-y rounded-md border border-krem-300 bg-kartu p-3 text-[13px] leading-6 text-teks-900 hover:border-teks-400 focus:border-biru-700 focus:ring-0">{{ $baris['kegiatan'] ?? '' }}</textarea>
                                </label>
                                <label class="block">
                                    <span class="mb-2 block text-[10px] font-semibold uppercase tracking-[0.1em] text-teks-500">Penanggung jawab</span>
                                    <input type="text" wire:model="item.{{ $i }}.penanggung_jawab" value="{{ $baris['penanggung_jawab'] ?? '' }}" class="w-full rounded-md border border-krem-300 bg-kartu p-3 text-[13px] text-teks-900 hover:border-teks-400 focus:border-biru-700 focus:ring-0">
                                </label>
                                <label class="block">
                                    <span class="mb-2 block text-[10px] font-semibold uppercase tracking-[0.1em] text-teks-500">Indikator keberhasilan</span>
                                    <textarea rows="3" wire:model="item.{{ $i }}.indikator_keberhasilan" class="w-full resize-y rounded-md border border-krem-300 bg-kartu p-3 text-[13px] leading-6 text-teks-900 hover:border-teks-400 focus:border-biru-700 focus:ring-0">{{ $baris['indikator_keberhasilan'] ?? '' }}</textarea>
                                </label>
                                <label class="block">
                                    <span class="mb-2 block text-[10px] font-semibold uppercase tracking-[0.1em] text-teks-500">Perkiraan waktu</span>
                                    <input type="text" wire:model="item.{{ $i }}.perkiraan_waktu" value="{{ $baris['perkiraan_waktu'] ?? '' }}" class="w-full rounded-md border border-krem-300 bg-kartu p-3 text-[13px] text-teks-900 hover:border-teks-400 focus:border-biru-700 focus:ring-0">
                                </label>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-4 text-[12px] text-teks-500">
                    <span>{{ count($item) }} butir rencana</span>
                    <span>Perubahan belum permanen sampai Anda menekan “Simpan”.</span>
                </div>
            @endif
        </section>
    @endif
</div>

<div class="flex flex-col gap-6">
    <x-kepala-halaman
        judul="Unggah Rapor Pendidikan sekolah"
        lead="Kepala sekolah dan tim kurikulum dapat mengunggah berkas Rapor Pendidikan satuan pendidikan untuk memperoleh analisis prioritas, akar masalah, dan draf rencana kerja dengan logika yang sama seperti tingkat daerah." />

    <x-kartu judul="Cara memperoleh berkas">
        <ol class="flex flex-col gap-2 text-[13px] text-teks-700">
            @foreach ([
                'Masuk ke raporpendidikan.dikdasmen.go.id dengan akun belajar.id sekolah Anda.',
                'Buka menu Unduh Rapor, pilih tahun terbaru.',
                'Unduh berkas berformat .xlsx. Jangan mengubah isinya.',
            ] as $n => $langkah)
                <li class="flex gap-3">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-krem-200 text-[11px] font-semibold text-teks-700 tabular">{{ $n + 1 }}</span>
                    <span>{{ $langkah }}</span>
                </li>
            @endforeach
        </ol>
        <p class="mt-4 rounded-md border border-sedang bg-sedang-bg px-3 py-2 text-[12px] leading-relaxed text-sedang">
            Catatan: mode satuan pendidikan belum diuji tim dengan berkas asli. Bila struktur berkas Anda
            berbeda dari yang diharapkan, sistem menolaknya dengan penjelasan, bukan menampilkan data keliru.
        </p>
    </x-kartu>

    @if ($this->impor === null)
        <x-kartu>
            <form wire:submit="proses" class="flex flex-col gap-4">
                <label class="flex cursor-pointer flex-col items-center gap-2 rounded-lg border border-dashed border-krem-300 bg-krem-100 px-6 py-10 text-center hover:border-teks-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-8 text-teks-400" aria-hidden="true">
                        <path d="M12 4v11m0-11L9 7m3-3l3 3M5 20h14" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    @if ($berkas)
                        <span class="text-[13px] font-medium text-teks-900">{{ $berkas->getClientOriginalName() }}</span>
                        <span class="text-[12px] text-teks-500">Klik untuk mengganti berkas</span>
                    @else
                        <span class="text-[13px] font-medium text-teks-900">Pilih berkas .xlsx</span>
                        <span class="text-[12px] text-teks-500">Berkas Rapor Pendidikan satuan pendidikan, maksimal 25 MB</span>
                    @endif
                    <input type="file" wire:model="berkas" accept=".xlsx" class="sr-only">
                </label>

                @error('berkas')
                    <p class="text-[13px] font-medium text-kurang">{{ $message }}</p>
                @enderror

                <div wire:loading wire:target="berkas" class="text-[13px] text-teks-500">Mengunggah berkas…</div>

                @if ($galat)
                    <div class="rounded-md border border-kurang bg-kurang-bg p-3">
                        <p class="text-[13px] font-semibold text-kurang">Berkas tidak dapat diproses</p>
                        <p class="mt-1 text-[13px] text-teks-700">{{ $galat }}</p>
                    </div>
                @endif

                <div>
                    <x-tombol jenis="primer" type="submit" wire:loading.attr="disabled" wire:target="proses,berkas">
                        Proses berkas
                    </x-tombol>
                </div>
            </form>
        </x-kartu>
    @else
        @php $impor = $this->impor; @endphp
        <div @if (in_array($impor->status, ['antre', 'proses'])) wire:poll.3s="periksaSelesai" @endif>
        <x-kartu>
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-[14px] font-semibold text-teks-900">{{ $impor->nama_berkas }}</p>
                    <p class="mt-0.5 text-[13px] text-teks-500">
                        @switch($impor->status)
                            @case('antre') Menunggu diproses… @break
                            @case('proses') Sedang membaca berkas… @break
                            @case('selesai') Selesai diproses. {{ $impor->jumlah_baris }} baris data. @break
                            @case('gagal') Gagal diproses. @break
                        @endswitch
                    </p>
                </div>
                <x-tombol jenis="sekunder" ukuran="kecil" wire:click="ulangi">Unggah berkas lain</x-tombol>
            </div>

            @if (in_array($impor->status, ['antre', 'proses']))
                <div class="mt-4 flex flex-col gap-2">
                    <div class="rangka-muat h-8"></div>
                    <div class="rangka-muat h-8 w-2/3"></div>
                </div>
            @endif

            @if ($impor->status === 'gagal' && $impor->catatan_galat)
                <div class="mt-3 rounded-md border border-kurang bg-kurang-bg p-3 text-[13px] text-teks-700">
                    {{ $impor->catatan_galat }}
                </div>
            @endif

            @if ($impor->status === 'selesai' && $this->wilayahSatuan)
                <div class="mt-4 border-t border-krem-300 pt-4">
                    <p class="text-[13px] text-teks-700">
                        Data <span class="font-medium text-teks-900">{{ $this->wilayahSatuan->nama_satuan }}</span> siap dianalisis.
                    </p>
                    <div class="mt-2">
                        <x-tombol jenis="primer" wire:click="keBeranda">Buka beranda sekolah</x-tombol>
                    </div>
                </div>
            @endif
        </x-kartu>
        </div>
    @endif
</div>

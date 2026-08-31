<div class="flex flex-col gap-6">
    <div>
        <h1 class="text-2xl font-bold text-teks-900">Unggah Rapor Pendidikan sekolah</h1>
        <p class="mt-1 max-w-3xl text-teks-700">
            Kepala sekolah dan tim dapat mengunggah berkas Rapor Pendidikan satuan pendidikan
            untuk memperoleh analisis prioritas, akar masalah, dan draf rencana tindak lanjut
            dengan logika yang sama seperti tingkat daerah.
        </p>
    </div>

    <div class="rounded-md border border-krem-300 bg-kartu p-5">
        <h2 class="text-[15px] font-semibold text-teks-900">Cara memperoleh berkas</h2>
        <ol class="mt-2 list-decimal space-y-1 pl-5 text-[13px] text-teks-700">
            <li>Masuk ke <span class="font-medium">raporpendidikan.dikdasmen.go.id</span> dengan akun belajar.id sekolah Anda.</li>
            <li>Buka menu <span class="font-medium">Unduh Rapor</span>, pilih tahun terbaru.</li>
            <li>Unduh berkas berformat <span class="font-medium">.xlsx</span>. Jangan mengubah isinya.</li>
        </ol>
        <p class="mt-3 rounded border border-sedang bg-sedang-bg p-2 text-[12px] text-sedang">
            Catatan: mode satuan pendidikan belum diuji tim dengan berkas asli. Bila struktur berkas Anda
            berbeda dari yang diharapkan, sistem akan menolaknya dengan penjelasan, bukan menampilkan data keliru.
        </p>
    </div>

    @if ($this->impor === null)
        <form wire:submit="proses" class="rounded-md border border-krem-300 bg-kartu p-5">
            <label class="flex flex-col gap-1 text-xs font-medium text-teks-700">
                Berkas Rapor Pendidikan (.xlsx, maksimal 25 MB)
                <input type="file" wire:model="berkas" accept=".xlsx"
                       class="mt-1 text-[13px] file:mr-3 file:rounded file:border file:border-biru-700 file:bg-kartu file:px-3 file:py-1.5 file:text-[13px] file:font-semibold file:text-biru-700">
            </label>
            @error('berkas')
                <p class="mt-2 text-[13px] font-medium text-kurang">{{ $message }}</p>
            @enderror

            <div wire:loading wire:target="berkas" class="mt-2 text-[13px] text-teks-500">Mengunggah berkas…</div>

            @if ($galat)
                <div class="mt-3 rounded border border-kurang bg-kurang-bg p-3">
                    <p class="text-[13px] font-semibold text-kurang">Berkas tidak dapat diproses</p>
                    <p class="mt-1 text-[13px] text-teks-700">{{ $galat }}</p>
                </div>
            @endif

            <button type="submit" wire:loading.attr="disabled" wire:target="proses,berkas"
                    class="mt-4 h-9 rounded bg-biru-700 px-4 text-[13px] font-semibold text-white hover:bg-biru-600 disabled:opacity-60">
                Proses berkas
            </button>
        </form>
    @else
        @php $impor = $this->impor; @endphp
        <div class="rounded-md border border-krem-300 bg-kartu p-5"
             @if (in_array($impor->status, ['antre', 'proses'])) wire:poll.3s @endif>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[15px] font-semibold text-teks-900">{{ $impor->nama_berkas }}</p>
                    <p class="mt-0.5 text-[13px] text-teks-500">
                        @switch($impor->status)
                            @case('antre') Menunggu diproses… @break
                            @case('proses') Sedang membaca berkas… @break
                            @case('selesai') Selesai diproses. {{ $impor->jumlah_baris }} baris data. @break
                            @case('gagal') Gagal diproses. @break
                        @endswitch
                    </p>
                </div>
                <button type="button" wire:click="ulangi"
                        class="h-8 rounded border border-biru-700 px-3 text-xs font-semibold text-biru-700">
                    Unggah berkas lain
                </button>
            </div>

            @if ($impor->status === 'gagal' && $impor->catatan_galat)
                <div class="mt-3 rounded border border-kurang bg-kurang-bg p-3 text-[13px] text-teks-700">
                    {{ $impor->catatan_galat }}
                </div>
            @endif

            @if ($impor->status === 'selesai' && $this->wilayahSatuan)
                <div class="mt-4 border-t border-krem-300 pt-4">
                    <p class="text-[13px] text-teks-700">
                        Data <span class="font-medium">{{ $this->wilayahSatuan->nama_satuan }}</span> siap dianalisis.
                    </p>
                    <a href="{{ route('dinas.prioritas') }}"
                       class="mt-2 inline-block h-9 rounded bg-biru-700 px-4 text-[13px] font-semibold leading-9 text-white hover:bg-biru-600">
                        Lihat prioritas masalah
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>

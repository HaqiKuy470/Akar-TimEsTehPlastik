<div class="flex flex-col gap-6">
    <div>
        <h1 class="text-2xl font-bold text-teks-900">Prioritas masalah</h1>
        <p class="mt-1 max-w-3xl text-teks-700">
            Indikator berlabel merah dan kuning diurutkan menurut skor prioritas, dari yang paling
            mendesak ditangani. Setiap skor dapat ditelusuri ke komponen pembentuknya, dan tiap
            indikator prioritas dapat ditelusuri akar masalahnya.
        </p>
    </div>

    {{-- Pemilih wilayah --}}
    <div class="rounded-md border border-krem-300 bg-kartu p-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <label class="flex flex-col gap-1 text-xs font-medium text-teks-700">
                Tahun data
                <select wire:model.live="tahun" class="h-9 rounded border border-krem-300 bg-white px-2 text-[13px] text-teks-900">
                    @foreach ($this->tahunTersedia as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </label>

            <label class="flex flex-col gap-1 text-xs font-medium text-teks-700">
                Provinsi
                <select wire:model.live="provinsi" class="h-9 rounded border border-krem-300 bg-white px-2 text-[13px] text-teks-900">
                    <option value="">— pilih —</option>
                    @foreach ($this->provinsiTersedia as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </label>

            <label class="flex flex-col gap-1 text-xs font-medium text-teks-700">
                Kabupaten/Kota
                <select wire:model.live="wilayahId" @disabled($provinsi === '')
                        class="h-9 rounded border border-krem-300 bg-white px-2 text-[13px] text-teks-900 disabled:bg-krem-100 disabled:text-teks-500">
                    <option value="">— pilih —</option>
                    @foreach ($this->kabkotaTersedia as $w)
                        <option value="{{ $w->id }}">{{ $w->kabupaten_kota }}</option>
                    @endforeach
                </select>
            </label>

            <label class="flex flex-col gap-1 text-xs font-medium text-teks-700">
                Jenjang
                <select wire:model.live="jenisSatuan" class="h-9 rounded border border-krem-300 bg-white px-2 text-[13px] text-teks-900">
                    <option value="">— pilih —</option>
                    @foreach ($this->jenisSatuanTersedia as $j)
                        <option value="{{ $j }}">{{ $j }}</option>
                    @endforeach
                </select>
            </label>

            <label class="flex flex-col gap-1 text-xs font-medium text-teks-700">
                Status satuan
                <select wire:model.live="statusSatuan" @disabled($jenisSatuan === '')
                        class="h-9 rounded border border-krem-300 bg-white px-2 text-[13px] text-teks-900 disabled:bg-krem-100 disabled:text-teks-500">
                    <option value="">— pilih —</option>
                    @foreach ($this->statusSatuanTersedia as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="mt-4 flex items-center gap-3">
            <button type="button" wire:click="jalankan" wire:loading.attr="disabled" @disabled(! $this->siapDijalankan)
                    class="h-9 rounded bg-biru-700 px-4 text-[13px] font-semibold text-white hover:bg-biru-600 disabled:cursor-not-allowed disabled:bg-krem-300 disabled:text-teks-500">
                {{ $sudahDijalankan ? 'Jalankan ulang analisis' : 'Jalankan analisis' }}
            </button>
            @unless ($this->siapDijalankan)
                <span class="text-[13px] text-teks-500">Lengkapi seluruh pilihan untuk menjalankan analisis.</span>
            @endunless
            <span wire:loading wire:target="jalankan" class="text-[13px] text-teks-500">Menghitung skor prioritas…</span>
        </div>

        @if ($sudahDijalankan && count($daftar) > 0)
            <div class="mt-3 flex flex-wrap items-center gap-3 border-t border-krem-300 pt-3">
                <span class="text-xs font-medium text-teks-500">Unduh laporan</span>
                <button type="button" wire:click="unduhPdf"
                        class="h-9 rounded border border-biru-700 bg-kartu px-4 text-[13px] font-semibold text-biru-700 hover:bg-biru-100">
                    Unduh PDF
                </button>
                <button type="button" wire:click="unduhExcel"
                        class="h-9 rounded border border-biru-700 bg-kartu px-4 text-[13px] font-semibold text-biru-700 hover:bg-biru-100">
                    Unduh Excel
                </button>
            </div>
        @endif
    </div>

    {{-- Kerangka saat analisis berjalan --}}
    <div wire:loading.delay.flex wire:target="jalankan" class="flex-col gap-3">
        @for ($i = 0; $i < 3; $i++)
            <div class="h-28 animate-pulse rounded-md border border-krem-300 bg-krem-200"></div>
        @endfor
    </div>

    <div wire:loading.remove wire:target="jalankan" class="flex flex-col gap-4">
        @if (! $this->siapDijalankan)
            <div class="rounded-md border border-krem-300 bg-kartu p-10 text-center">
                <p class="text-teks-700">Pilih wilayah, jenjang, dan status satuan untuk memulai.</p>
            </div>
        @elseif (! $sudahDijalankan)
            <div class="rounded-md border border-krem-300 bg-kartu p-10 text-center">
                <p class="text-teks-700">Belum ada analisis untuk kombinasi ini.</p>
                <p class="mt-1 text-[13px] text-teks-500">Klik “Jalankan analisis” untuk menghitung skor prioritas.</p>
            </div>
        @elseif (count($daftar) === 0)
            <div class="rounded-md border border-baik bg-baik-bg p-6">
                <p class="font-semibold text-baik">Tidak ada indikator bermasalah</p>
                <p class="mt-1 text-[13px] text-teks-700">
                    Tidak ditemukan indikator berlabel Kurang atau Sedang untuk kombinasi ini.
                    Pertahankan praktik yang sudah berjalan.
                </p>
            </div>
        @else
            <p class="text-[13px] text-teks-500">{{ count($daftar) }} indikator masuk daftar prioritas.</p>

            @foreach ($daftar as $item)
                <div wire:key="prioritas-{{ $item['id'] }}" class="rounded-md border border-krem-300 bg-kartu p-5">
                    <div class="flex items-start gap-4">
                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-biru-700 text-xs font-bold text-white tabular">
                            {{ $item['peringkat_prioritas'] }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <h2 class="text-[15px] font-semibold text-teks-900">
                                    {{ $item['nomor'] }} {{ $item['nama'] }}
                                </h2>
                                <div class="shrink-0 text-right">
                                    <div class="tabular text-[32px] font-bold leading-none text-teks-900">{{ rtrim(rtrim(number_format((float) $item['skor'], 1, ',', '.'), '0'), ',') }}</div>
                                    <div class="text-[11px] font-medium uppercase text-teks-500">Skor prioritas</div>
                                </div>
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1">
                                <x-badge-capaian :label="$item['label']" />
                                <x-arah-perubahan :nilai="$item['perubahan']" />
                                @if ($item['peringkat_teks'])
                                    <span class="text-xs font-medium text-teks-700">{{ $item['peringkat_teks'] }} kabupaten/kota</span>
                                @endif
                            </div>

                            <p class="mt-2 max-w-3xl text-[13px] text-teks-700">{{ $item['kalimat_penjelas'] }}</p>

                            <div class="mt-3 flex flex-wrap gap-4 text-[13px] font-medium text-biru-700">
                                <button type="button" wire:click="toggleRincian({{ $item['id'] }})" class="hover:text-biru-600">
                                    {{ $item['rincian_terbuka'] ? '▾' : '▸' }} Rincian skor
                                </button>
                                <button type="button" wire:click="toggleAkar({{ $item['id'] }})" class="hover:text-biru-600">
                                    {{ $item['akar_terbuka'] ? '▾' : '▸' }} Telusuri akar masalah
                                </button>
                            </div>

                            @if ($item['rincian_terbuka'])
                                <div class="mt-3">
                                    <x-rincian-skor :komponen="$item['komponen_skor']" :skor="$item['skor']" />
                                </div>
                            @endif

                            @if ($item['akar_terbuka'] && $item['akar'])
                                <div class="mt-3 rounded border border-krem-300 bg-krem-100 p-4">
                                    @if (! $item['akar']['dipetakan'])
                                        <p class="text-[13px] text-teks-700">
                                            Rekomendasi akar masalah belum tersedia untuk indikator ini.
                                            Indikator ini belum termasuk 15–20 indikator prioritas yang dipetakan pada tahap ini.
                                        </p>
                                    @else
                                        {{-- Pohon akar masalah: daftar bertingkat dengan garis penghubung (DESIGN.md 5) --}}
                                        <div class="flex items-center gap-2 text-[13px] font-semibold text-teks-900">
                                            <span>{{ $item['nomor'] }} {{ $item['nama'] }}</span>
                                            <x-badge-capaian :label="$item['akar']['induk_label']" />
                                        </div>

                                        @foreach ($item['akar']['kandidat'] as $kIndex => $kandidat)
                                            <div class="mt-3 border-l-2 border-krem-300 pl-4">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-[13px] font-semibold text-teks-900">{{ $kandidat['label'] }}</span>
                                                    <span @class([
                                                        'rounded border px-2 py-0.5 text-[11px] font-semibold',
                                                        'border-kurang text-kurang' => $kandidat['keyakinan_kode'] === 'kuat',
                                                        'border-sedang text-sedang' => $kandidat['keyakinan_kode'] === 'sedang',
                                                        'border-kosong text-kosong' => in_array($kandidat['keyakinan_kode'], ['lemah', 'tidak_cukup_bukti']),
                                                    ])>{{ $kandidat['keyakinan'] }}</span>
                                                    @if ($kIndex === 0 && $kandidat['keyakinan_kode'] !== 'tidak_cukup_bukti')
                                                        <span class="text-[11px] font-medium uppercase text-emas-700">Akar terkuat</span>
                                                    @endif
                                                </div>

                                                @if (count($kandidat['bukti']) > 0)
                                                    <ul class="mt-2 flex flex-col gap-1">
                                                        @foreach ($kandidat['bukti'] as $bukti)
                                                            <li class="flex flex-wrap items-center gap-2 rounded bg-biru-100 px-2 py-1 text-[13px]">
                                                                <span class="font-medium text-teks-900">{{ $bukti['nomor'] }}</span>
                                                                <span class="text-teks-700">{{ $bukti['nama'] }}</span>
                                                                <x-badge-capaian :label="$bukti['label']" />
                                                                <span class="text-[11px] font-medium text-biru-700">&larr; bukti</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="mt-1 text-[13px] text-teks-500">
                                                        Belum ada indikator pendukung yang berlabel Kurang atau Sedang.
                                                    </p>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

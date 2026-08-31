<div class="flex flex-col gap-6">
    <x-kepala-halaman
        judul="Prioritas masalah"
        lead="Indikator berlabel merah dan kuning diurutkan menurut skor prioritas, dari yang paling mendesak. Setiap skor dapat ditelusuri ke komponen pembentuknya, dan tiap indikator prioritas dapat ditelusuri akar masalahnya." />

    <x-kartu rapat>
        <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-5">
            <x-pilih label="Tahun data" wire:model.live="tahun">
                @foreach ($this->tahunTersedia as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </x-pilih>

            <x-pilih label="Provinsi" wire:model.live="provinsi">
                <option value="">— pilih —</option>
                @foreach ($this->provinsiTersedia as $p)
                    <option value="{{ $p }}">{{ $p }}</option>
                @endforeach
            </x-pilih>

            <x-pilih label="Kabupaten/Kota" wire:model.live="wilayahId" :disabled="$provinsi === ''">
                <option value="">— pilih —</option>
                @foreach ($this->kabkotaTersedia as $w)
                    <option value="{{ $w->id }}">{{ $w->kabupaten_kota }}</option>
                @endforeach
            </x-pilih>

            <x-pilih label="Jenjang" wire:model.live="jenisSatuan">
                <option value="">— pilih —</option>
                @foreach ($this->jenisSatuanTersedia as $j)
                    <option value="{{ $j }}">{{ $j }}</option>
                @endforeach
            </x-pilih>

            <x-pilih label="Status satuan" wire:model.live="statusSatuan" :disabled="$jenisSatuan === ''">
                <option value="">— pilih —</option>
                @foreach ($this->statusSatuanTersedia as $s)
                    <option value="{{ $s }}">{{ $s }}</option>
                @endforeach
            </x-pilih>
        </div>

        <div class="flex flex-wrap items-center gap-3 border-t border-krem-300 px-4 py-3">
            <x-tombol wire:click="jalankan" wire:loading.attr="disabled" :disabled="! $this->siapDijalankan">
                {{ $sudahDijalankan ? 'Jalankan ulang analisis' : 'Jalankan analisis' }}
            </x-tombol>
            @unless ($this->siapDijalankan)
                <span class="text-[13px] text-teks-500">Lengkapi seluruh pilihan untuk menjalankan analisis.</span>
            @endunless
            <span wire:loading wire:target="jalankan" class="text-[13px] text-teks-500">Menghitung skor prioritas…</span>

            @if ($sudahDijalankan && count($daftar) > 0)
                <span class="ml-auto flex items-center gap-2">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Unduh</span>
                    <x-tombol jenis="sekunder" ukuran="kecil" wire:click="unduhPdf">PDF</x-tombol>
                    <x-tombol jenis="sekunder" ukuran="kecil" wire:click="unduhExcel">Excel</x-tombol>
                </span>
            @endif
        </div>
    </x-kartu>

    <div wire:loading.delay.flex wire:target="jalankan" class="flex-col gap-3">
        <div class="rangka-muat h-28"></div>
        <div class="rangka-muat h-28"></div>
        <div class="rangka-muat h-28"></div>
    </div>

    <div wire:loading.remove wire:target="jalankan" class="flex flex-col gap-4">
        @if (! $this->siapDijalankan)
            <x-kartu rapat>
                <x-kosong ikon="filter" judul="Pilih wilayah lebih dulu"
                          pesan="Pilih wilayah, jenjang, dan status satuan pendidikan untuk memulai analisis." />
            </x-kartu>
        @elseif (! $sudahDijalankan)
            <x-kartu rapat>
                <x-kosong ikon="dokumen" judul="Belum ada analisis untuk kombinasi ini"
                          pesan="Tekan “Jalankan analisis” untuk menghitung skor prioritas seluruh indikator bermasalah.">
                    <x-slot:aksi>
                        <x-tombol wire:click="jalankan" :disabled="! $this->siapDijalankan">Jalankan analisis</x-tombol>
                    </x-slot:aksi>
                </x-kosong>
            </x-kartu>
        @elseif (count($daftar) === 0)
            <x-kartu>
                <p class="text-[14px] font-semibold text-baik">Tidak ada indikator bermasalah</p>
                <p class="mt-1 text-[13px] text-teks-700">
                    Tidak ditemukan indikator berlabel Kurang atau Sedang untuk kombinasi ini.
                    Pertahankan praktik yang sudah berjalan.
                </p>
            </x-kartu>
        @else
            <x-judul-bagian judul="Daftar prioritas" :jumlah="count($daftar).' indikator masuk daftar prioritas'" />

            @foreach ($daftar as $item)
                @php $utama = $item['peringkat_prioritas'] == 1; @endphp
                <div wire:key="prioritas-{{ $item['id'] }}"
                     class="rounded-[--radius-kartu] border border-krem-300 bg-kartu p-5">
                    <div class="flex items-start gap-4">
                        <span @class([
                            'mt-0.5 grid size-7 shrink-0 place-items-center rounded-full text-[12px] font-semibold text-white tabular',
                            'bg-emas-700' => $utama,
                            'bg-biru-700' => ! $utama,
                        ])>{{ $item['peringkat_prioritas'] }}</span>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <h3 class="text-[15px] font-semibold leading-snug text-teks-900">
                                    {{ $item['nomor'] }} {{ $item['nama'] }}
                                </h3>
                                <div class="shrink-0 text-right">
                                    <div class="tabular text-[30px] font-bold leading-none tracking-tight text-teks-900">{{ rtrim(rtrim(number_format((float) $item['skor'], 1, ',', '.'), '0'), ',') }}</div>
                                    <div class="mt-0.5 text-[10px] font-semibold uppercase tracking-[0.05em] text-teks-400">Skor prioritas</div>
                                </div>
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5">
                                <x-badge-capaian :label="$item['label']" />
                                <x-arah-perubahan :nilai="$item['perubahan']" />
                                @if ($item['peringkat_teks'])
                                    <span class="text-[12px] text-teks-500">{{ $item['peringkat_teks'] }} kabupaten/kota</span>
                                @endif
                            </div>

                            <p class="mt-2.5 max-w-3xl text-[13px] leading-relaxed text-teks-700">{{ $item['kalimat_penjelas'] }}</p>

                            <div class="mt-3 flex flex-wrap gap-4">
                                <x-tombol jenis="tersier" ukuran="kecil" wire:click="toggleRincian({{ $item['id'] }})">
                                    {{ $item['rincian_terbuka'] ? '▾' : '▸' }} Rincian skor
                                </x-tombol>
                                <x-tombol jenis="tersier" ukuran="kecil" wire:click="toggleAkar({{ $item['id'] }})">
                                    {{ $item['akar_terbuka'] ? '▾' : '▸' }} Telusuri akar masalah
                                </x-tombol>
                            </div>

                            @if ($item['rincian_terbuka'])
                                <div class="mt-3">
                                    <x-rincian-skor :komponen="$item['komponen_skor']" :skor="$item['skor']" />
                                </div>
                            @endif

                            @if ($item['akar_terbuka'] && $item['akar'])
                                <div class="mt-3 rounded-md border border-krem-300 bg-krem-100 p-4">
                                    @if (! $item['akar']['dipetakan'])
                                        <p class="text-[13px] text-teks-700">
                                            Rekomendasi akar masalah belum tersedia untuk indikator ini.
                                            Indikator ini belum termasuk 15–20 indikator prioritas yang dipetakan pada tahap ini.
                                        </p>
                                    @else
                                        <div class="flex items-center gap-2 text-[13px] font-semibold text-teks-900">
                                            <span>{{ $item['nomor'] }} {{ $item['nama'] }}</span>
                                            <x-badge-capaian :label="$item['akar']['induk_label']" />
                                        </div>

                                        @foreach ($item['akar']['kandidat'] as $kIndex => $kandidat)
                                            <div class="mt-3 border-l-2 border-krem-300 pl-4">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-[13px] font-semibold text-teks-900">{{ $kandidat['label'] }}</span>
                                                    <span @class([
                                                        'rounded border px-1.5 py-0.5 text-[10px] font-semibold leading-none',
                                                        'border-kurang text-kurang' => $kandidat['keyakinan_kode'] === 'kuat',
                                                        'border-sedang text-sedang' => $kandidat['keyakinan_kode'] === 'sedang',
                                                        'border-kosong text-kosong' => in_array($kandidat['keyakinan_kode'], ['lemah', 'tidak_cukup_bukti']),
                                                    ])>{{ $kandidat['keyakinan'] }}</span>
                                                    @if ($kIndex === 0 && $kandidat['keyakinan_kode'] !== 'tidak_cukup_bukti')
                                                        <span class="text-[10px] font-semibold uppercase tracking-[0.05em] text-emas-700">Akar terkuat</span>
                                                    @endif
                                                </div>

                                                @if (count($kandidat['bukti']) > 0)
                                                    <ul class="mt-2 flex flex-col gap-1">
                                                        @foreach ($kandidat['bukti'] as $bukti)
                                                            <li class="flex flex-wrap items-center gap-2 rounded bg-biru-100 px-2 py-1.5 text-[12px]">
                                                                <span class="font-medium text-teks-900">{{ $bukti['nomor'] }}</span>
                                                                <span class="text-teks-700">{{ $bukti['nama'] }}</span>
                                                                <x-badge-capaian :label="$bukti['label']" />
                                                                <span class="text-[10px] font-semibold text-biru-700">&larr; bukti</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="mt-1 text-[12px] text-teks-500">
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

<div class="flex flex-col gap-6">
    <x-kepala-halaman
        judul="Prioritas & akar masalah"
        lead="Indikator sekolah berlabel merah dan kuning, diurutkan menurut skor prioritas. Setiap skor dapat ditelusuri ke komponen pembentuknya, dan tiap indikator dapat ditelusuri akar masalahnya." />

    @if ($this->sekolah === null)
        <x-kartu rapat>
            <x-kosong
                ikon="unggah"
                judul="Belum ada berkas Rapor Pendidikan sekolah"
                pesan="Unggah berkas dari akun belajar.id sekolah Anda untuk menjalankan analisis.">
                <x-slot:aksi>
                    <x-tombol jenis="primer" :href="route('sekolah.unggah')">Unggah berkas</x-tombol>
                </x-slot:aksi>
            </x-kosong>
        </x-kartu>
    @else
        <x-kartu rapat>
            <div class="flex flex-wrap items-center gap-3 p-4">
                <x-tombol jenis="primer" wire:click="jalankan" wire:loading.attr="disabled" wire:target="jalankan">
                    {{ $sudahDijalankan ? 'Jalankan ulang analisis' : 'Jalankan analisis' }}
                </x-tombol>
                <span wire:loading wire:target="jalankan" class="text-[13px] text-teks-500">Menghitung skor prioritas…</span>
            </div>
        </x-kartu>

        <div wire:loading.delay.flex wire:target="jalankan" class="flex-col gap-3">
            <div class="rangka-muat h-28"></div>
            <div class="rangka-muat h-28"></div>
            <div class="rangka-muat h-28"></div>
        </div>

        <div wire:loading.remove wire:target="jalankan" class="flex flex-col gap-4">
            @if (! $sudahDijalankan)
                <x-kartu rapat>
                    <x-kosong
                        ikon="grafik"
                        judul="Belum ada analisis untuk sekolah ini"
                        pesan="Tekan “Jalankan analisis” untuk menghitung skor prioritas dari capaian sekolah." />
                </x-kartu>
            @elseif (count($daftar) === 0)
                <x-kartu>
                    <p class="text-[14px] font-semibold text-baik">Tidak ada indikator bermasalah</p>
                    <p class="mt-1 text-[13px] text-teks-700">
                        Tidak ditemukan indikator berlabel Kurang atau Sedang. Pertahankan praktik yang sudah berjalan.
                    </p>
                </x-kartu>
            @else
                <p class="text-[12px] text-teks-500">{{ count($daftar) }} indikator masuk daftar prioritas.</p>

                @foreach ($daftar as $item)
                    @php
                        $pembanding = collect($item['komponen_skor'])->first(fn ($k) => ! empty($k['pembanding']['tersedia']))['pembanding'] ?? null;
                    @endphp
                    <x-kartu wire:key="prioritas-{{ $item['id'] }}">
                        <div class="flex items-start gap-4">
                            <span @class([
                                'mt-0.5 grid size-7 shrink-0 place-items-center rounded-full text-[12px] font-bold text-white tabular',
                                'bg-emas-700' => $item['peringkat_prioritas'] === 1,
                                'bg-biru-700' => $item['peringkat_prioritas'] !== 1,
                            ])>{{ $item['peringkat_prioritas'] }}</span>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-4">
                                    <h2 class="text-[15px] font-semibold text-teks-900">{{ $item['nomor'] }} {{ $item['nama'] }}</h2>
                                    <div class="shrink-0 text-right">
                                        <div class="tabular text-[30px] font-bold leading-none tracking-tight text-teks-900">{{ rtrim(rtrim(number_format((float) $item['skor'], 1, ',', '.'), '0'), ',') }}</div>
                                        <div class="text-[11px] font-medium uppercase tracking-[0.04em] text-teks-500">Skor prioritas</div>
                                    </div>
                                </div>

                                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5">
                                    <x-badge-capaian :label="$item['label']" />
                                    <x-arah-perubahan :nilai="$item['perubahan']" />
                                    @if ($pembanding)
                                        <span class="inline-flex items-center gap-1.5 text-[12px] text-teks-500">
                                            vs rata-rata {{ $pembanding['nama'] }}:
                                            <x-badge-capaian :label="$pembanding['label']" />
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-2 max-w-3xl text-[13px] leading-relaxed text-teks-700">{{ $item['kalimat_penjelas'] }}</p>

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
                                            </p>
                                        @else
                                            <div class="flex flex-wrap items-center gap-2 text-[13px] font-semibold text-teks-900">
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
                                                            <span class="text-[11px] font-medium uppercase tracking-[0.04em] text-emas-700">Akar terkuat</span>
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
                    </x-kartu>
                @endforeach

                <x-kartu>
                    <p class="text-[13px] text-teks-700">
                        Lanjutkan ke
                        <a href="{{ route('sekolah.rkt') }}" class="font-semibold text-biru-700 hover:underline">Rencana Kerja Tahunan</a>
                        untuk menyusun draf kegiatan dari hasil analisis ini.
                    </p>
                </x-kartu>
            @endif
        </div>
    @endif
</div>

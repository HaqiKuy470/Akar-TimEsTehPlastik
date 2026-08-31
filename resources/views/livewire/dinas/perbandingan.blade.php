<div class="flex flex-col gap-6">
    <div>
        <h1 class="text-2xl font-bold text-teks-900">Perbandingan antardaerah</h1>
        <p class="mt-1 max-w-3xl text-teks-700">
            Posisi satu kabupaten/kota terhadap seluruh daerah lain di provinsinya pada satu
            indikator, beserta pembanding agregat provinsi dan nasional. Perbandingan ini
            tidak tersedia di portal resmi Rapor Pendidikan.
        </p>
    </div>

    {{-- Pemilih --}}
    <div class="rounded-md border border-krem-300 bg-kartu p-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
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

            <label class="flex flex-col gap-1 text-xs font-medium text-teks-700">
                Indikator
                <select wire:model.live="indikatorId" @disabled($jenisSatuan === '')
                        class="h-9 rounded border border-krem-300 bg-white px-2 text-[13px] text-teks-900 disabled:bg-krem-100 disabled:text-teks-500">
                    @foreach ($this->indikatorTersedia as $i)
                        <option value="{{ $i->id }}">{{ $i->nomor }} — {{ $i->nama }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    @php $hasil = $this->hasil; @endphp

    @if ($hasil === null)
        <div class="rounded-md border border-krem-300 bg-kartu p-10 text-center">
            <p class="text-teks-700">Lengkapi pilihan di atas untuk membandingkan daerah.</p>
            <p class="mt-1 text-[13px] text-teks-500">Pilih provinsi, kabupaten/kota, jenjang, status, dan indikator.</p>
        </div>
    @else
        @php
            $p = $hasil['peringkat'];
            $band = $hasil['pembanding'];
        @endphp

        <p class="text-[13px] text-teks-500">
            {{ $hasil['indikator']['nomor'] }} · {{ $hasil['indikator']['nama'] }}
        </p>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            {{-- Kartu posisi wilayah --}}
            <div class="rounded-md border border-krem-300 bg-kartu p-5">
                <h2 class="text-[15px] font-semibold text-teks-900">Posisi {{ $band['wilayah']['nama'] }}</h2>

                @if ($p['peringkat'] === null)
                    <p class="mt-3 text-[13px] text-teks-700">{{ $p['catatan'] ?? 'Data tidak tersedia untuk indikator ini.' }}</p>
                    @if ($p['dari'] > 0)
                        <p class="mt-1 text-[13px] text-teks-500">{{ $p['dari'] }} kabupaten/kota lain memiliki data pada indikator ini.</p>
                    @endif
                @else
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="tabular text-[32px] font-bold leading-none text-teks-900">
                            @if ($p['peringkat'] === $p['peringkat_hingga'])
                                {{ $p['peringkat'] }}
                            @else
                                {{ $p['peringkat'] }}–{{ $p['peringkat_hingga'] }}
                            @endif
                        </span>
                        <span class="text-[13px] text-teks-500">dari {{ $p['dari'] }} kabupaten/kota di {{ $provinsi }}</span>
                    </div>
                    <div class="mt-3 flex items-center gap-3">
                        <x-badge-capaian :label="$p['label_wilayah']" />
                        <x-arah-perubahan :nilai="$p['perubahan_wilayah']" />
                    </div>
                    @if ($p['persentil'] !== null)
                        <p class="mt-3 text-[13px] text-teks-700">
                            Lebih baik dari sekitar {{ round($p['persentil'] * 100) }}% kabupaten/kota lain di provinsi yang sama.
                        </p>
                    @endif
                @endif
            </div>

            {{-- Kartu pembanding --}}
            <div class="rounded-md border border-krem-300 bg-kartu p-5">
                <h2 class="text-[15px] font-semibold text-teks-900">Pembanding</h2>
                <div class="mt-3 flex flex-col divide-y divide-krem-300">
                    @foreach ([
                        ['nama' => $band['wilayah']['nama'], 'label' => $band['wilayah']['label'], 'perubahan' => $band['wilayah']['perubahan'], 'tersedia' => $band['wilayah']['label'] !== null],
                        ['nama' => 'Agregat provinsi', 'label' => $band['provinsi']['label'], 'perubahan' => $band['provinsi']['perubahan'], 'tersedia' => $band['provinsi']['tersedia'] && $band['provinsi']['label'] !== null],
                        ['nama' => 'Nasional', 'label' => $band['nasional']['label'], 'perubahan' => $band['nasional']['perubahan'], 'tersedia' => $band['nasional']['tersedia'] && $band['nasional']['label'] !== null],
                    ] as $baris)
                        <div class="flex items-center justify-between gap-3 py-2.5 text-[13px]">
                            <span class="text-teks-900">{{ $baris['nama'] }}</span>
                            @if ($baris['tersedia'])
                                <span class="flex items-center gap-3">
                                    <x-badge-capaian :label="$baris['label']" />
                                    <x-arah-perubahan :nilai="$baris['perubahan']" />
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded border border-kosong bg-kosong-bg px-2.5 py-1 text-xs font-semibold text-kosong">
                                    <span aria-hidden="true">–</span>Tidak tersedia
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Tabel peringkat --}}
        <div class="overflow-hidden rounded-md border border-krem-300 bg-kartu">
            <div class="border-b border-krem-300 bg-krem-200 px-5 py-3">
                <h2 class="text-[15px] font-semibold text-teks-900">Peringkat seluruh kabupaten/kota di {{ $provinsi }}</h2>
            </div>
            @if (empty($hasil['tabel']))
                <p class="px-5 py-4 text-[13px] text-teks-500">Tidak ada kabupaten/kota dengan data pada indikator ini.</p>
            @else
                <div class="max-h-[32rem] overflow-auto">
                    <table class="w-full text-[13px]">
                        <thead class="sticky top-0 z-10">
                            <tr class="border-b border-krem-300 bg-krem-200 text-left text-xs font-semibold uppercase text-teks-700">
                                <th scope="col" class="w-20 px-5 py-2 text-right">
                                    <button type="button" wire:click="urutkan('peringkat')" class="inline-flex items-center gap-1 uppercase">
                                        Peringkat
                                        @if ($urutKolom === 'peringkat')<span aria-hidden="true">{{ $urutArah === 'asc' ? '▲' : '▼' }}</span>@endif
                                    </button>
                                </th>
                                <th scope="col" class="px-5 py-2">
                                    <button type="button" wire:click="urutkan('nama')" class="inline-flex items-center gap-1 uppercase">
                                        Kabupaten/Kota
                                        @if ($urutKolom === 'nama')<span aria-hidden="true">{{ $urutArah === 'asc' ? '▲' : '▼' }}</span>@endif
                                    </button>
                                </th>
                                <th scope="col" class="px-5 py-2">Capaian</th>
                                <th scope="col" class="px-5 py-2">Perubahan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hasil['tabel'] as $baris)
                                <tr @class([
                                    'border-b border-krem-300 last:border-0',
                                    'bg-biru-100' => $baris['wilayah_id'] === $wilayahId,
                                    'hover:bg-krem-200' => $baris['wilayah_id'] !== $wilayahId,
                                ])>
                                    <td class="px-5 py-2.5 text-right tabular text-teks-900">{{ $baris['peringkat'] }}</td>
                                    <td class="px-5 py-2.5 text-teks-900">
                                        {{ $baris['nama'] }}
                                        @if ($baris['wilayah_id'] === $wilayahId)
                                            <span class="ml-1 text-xs text-biru-700">← daerah dipilih</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-2.5"><x-badge-capaian :label="$baris['label_capaian']" /></td>
                                    <td class="px-5 py-2.5"><x-arah-perubahan :nilai="$baris['perubahan_nilai']" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>

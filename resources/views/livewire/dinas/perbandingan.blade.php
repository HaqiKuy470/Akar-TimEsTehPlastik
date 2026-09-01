<div class="flex flex-col gap-6">
    <x-kepala-halaman
        judul="Perbandingan antardaerah"
        lead="Posisi satu kabupaten/kota terhadap seluruh daerah lain di provinsinya pada satu indikator, beserta pembanding agregat provinsi dan nasional. Perbandingan ini tidak tersedia di portal resmi Rapor Pendidikan." />

    <x-kartu rapat>
        <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3">
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

            <x-pilih label="Indikator" wire:model.live="indikatorId" :disabled="$jenisSatuan === ''">
                @foreach ($this->indikatorTersedia as $i)
                    <option value="{{ $i->id }}">{{ $i->nomor }} — {{ $i->nama }}</option>
                @endforeach
            </x-pilih>
        </div>
    </x-kartu>

    @php $hasil = $this->hasil; @endphp

    @if ($hasil === null)
        <x-kartu rapat>
            <x-kosong ikon="filter" judul="Lengkapi pilihan di atas"
                      pesan="Pilih provinsi, kabupaten/kota, jenjang, status, dan indikator untuk membandingkan daerah." />
        </x-kartu>
    @else
        @php
            $p = $hasil['peringkat'];
            $band = $hasil['pembanding'];
        @endphp

        <x-judul-bagian :judul="$hasil['indikator']['nomor'].' · '.$hasil['indikator']['nama']" />

        <div class="grid gap-4 lg:grid-cols-2">
            <x-kartu :judul="'Posisi '.$band['wilayah']['nama']">
                @if ($p['peringkat'] === null)
                    <p class="text-[13px] text-teks-700">{{ $p['catatan'] ?? 'Data tidak tersedia untuk indikator ini.' }}</p>
                    @if ($p['dari'] > 0)
                        <p class="mt-1 text-[13px] text-teks-500">{{ $p['dari'] }} kabupaten/kota lain memiliki data pada indikator ini.</p>
                    @endif
                @else
                    <div class="flex items-baseline gap-2">
                        <span class="tabular text-[30px] font-bold leading-none tracking-tight text-teks-900">
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
            </x-kartu>

            <x-kartu judul="Pembanding">
                <div class="flex flex-col divide-y divide-krem-300">
                    @foreach ([
                        ['nama' => $band['wilayah']['nama'], 'label' => $band['wilayah']['label'], 'perubahan' => $band['wilayah']['perubahan'], 'tersedia' => $band['wilayah']['label'] !== null],
                        ['nama' => 'Agregat provinsi', 'label' => $band['provinsi']['label'], 'perubahan' => $band['provinsi']['perubahan'], 'tersedia' => $band['provinsi']['tersedia'] && $band['provinsi']['label'] !== null],
                        ['nama' => 'Nasional', 'label' => $band['nasional']['label'], 'perubahan' => $band['nasional']['perubahan'], 'tersedia' => $band['nasional']['tersedia'] && $band['nasional']['label'] !== null],
                    ] as $baris)
                        <div class="flex items-center justify-between gap-3 py-2.5 text-[13px] first:pt-0 last:pb-0">
                            <span class="text-teks-900">{{ $baris['nama'] }}</span>
                            @if ($baris['tersedia'])
                                <span class="flex items-center gap-3">
                                    <x-badge-capaian :label="$baris['label']" />
                                    <x-arah-perubahan :nilai="$baris['perubahan']" />
                                </span>
                            @else
                                <x-badge-capaian label="Tidak Tersedia" />
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-kartu>
        </div>

        <div class="flex flex-col gap-3">
            <x-judul-bagian :judul="'Peringkat kabupaten/kota di '.$provinsi" :jumlah="count($hasil['tabel'] ?? []).' daerah'" />

            @if (! empty($hasil['tabel']))
                @php
                    $grafikBaris = collect($hasil['tabel'])
                        ->sortBy('peringkat')
                        ->map(fn ($b) => [
                            'nama' => $b['nama'],
                            'label' => $b['label_capaian'],
                            'perubahan' => $b['perubahan_nilai'],
                            'peringkat' => $b['peringkat'],
                            'terpilih' => $b['wilayah_id'] === $wilayahId,
                        ])->values()->all();
                @endphp
                <x-kartu wire:key="grafik-{{ $hasil['indikator']['nomor'] }}-{{ $wilayahId }}">
                    <div wire:ignore x-data="grafikPeringkat(@js(['baris' => $grafikBaris]))" x-init="gambar()">
                        <div class="mb-3 flex items-center gap-3">
                            <h3 class="text-[13px] font-semibold uppercase tracking-[0.05em] text-teks-700">Semua daerah pada indikator ini</h3>
                            <span class="h-px flex-1 bg-krem-300"></span>
                        </div>
                        <div class="w-full overflow-x-auto">
                            <div style="height: {{ max(count($grafikBaris) * 22 + 40, 160) }}px; min-width: 460px;">
                                <canvas x-ref="kanvas"></canvas>
                            </div>
                        </div>
                        <p class="mt-2 text-[11px] text-teks-400">Garis tebal menandai daerah yang dipilih. Arahkan kursor untuk detail peringkat.</p>
                    </div>
                </x-kartu>
            @endif

            <x-kartu rapat>
                @if (empty($hasil['tabel']))
                    <p class="px-5 py-4 text-[13px] text-teks-500">Tidak ada kabupaten/kota dengan data pada indikator ini.</p>
                @else
                    <div class="max-h-[32rem] overflow-auto">
                        <table class="w-full text-[13px]">
                            <thead class="sticky top-0 z-10">
                                <tr class="border-b border-krem-300 bg-krem-200 text-left">
                                    <th scope="col" class="w-20 px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">
                                        <button type="button" wire:click="urutkan('peringkat')" class="inline-flex items-center gap-1 uppercase">
                                            Peringkat
                                            @if ($urutKolom === 'peringkat')<span aria-hidden="true">{{ $urutArah === 'asc' ? '▲' : '▼' }}</span>@endif
                                        </button>
                                    </th>
                                    <th scope="col" class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">
                                        <button type="button" wire:click="urutkan('nama')" class="inline-flex items-center gap-1 uppercase">
                                            Kabupaten/Kota
                                            @if ($urutKolom === 'nama')<span aria-hidden="true">{{ $urutArah === 'asc' ? '▲' : '▼' }}</span>@endif
                                        </button>
                                    </th>
                                    <th scope="col" class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Capaian</th>
                                    <th scope="col" class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Perubahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($hasil['tabel'] as $baris)
                                    <tr @class([
                                        'border-b border-krem-300 last:border-0',
                                        'bg-biru-100' => $baris['wilayah_id'] === $wilayahId,
                                        'hover:bg-krem-150' => $baris['wilayah_id'] !== $wilayahId,
                                    ])>
                                        <td class="px-4 py-2.5 text-right tabular text-teks-900">{{ $baris['peringkat'] }}</td>
                                        <td class="px-4 py-2.5 text-teks-900">
                                            {{ $baris['nama'] }}
                                            @if ($baris['wilayah_id'] === $wilayahId)
                                                <span class="ml-1 text-[11px] font-medium text-biru-700">← daerah dipilih</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5"><x-badge-capaian :label="$baris['label_capaian']" /></td>
                                        <td class="px-4 py-2.5"><x-arah-perubahan :nilai="$baris['perubahan_nilai']" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-kartu>
        </div>
    @endif

    <x-grafik-skrip />
</div>

<div class="flex flex-col gap-6">
    <div>
        <h1 class="text-2xl font-bold text-teks-900">Tren lintas tahun</h1>
        <p class="mt-1 max-w-3xl text-teks-700">
            Pergerakan label capaian dari edisi ke edisi. Portal resmi menampilkan capaian satu tahun;
            di sini terlihat lintasannya, termasuk indikator yang memburuk dua tahun berturut-turut.
        </p>
    </div>

    {{-- Pemilih wilayah --}}
    <div class="rounded-md border border-krem-300 bg-kartu p-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
    </div>

    @php $tren = $this->tren; @endphp

    @if ($tren === null)
        <div class="rounded-md border border-krem-300 bg-kartu p-10 text-center">
            <p class="text-teks-700">Lengkapi pilihan di atas untuk menampilkan tren.</p>
        </div>
    @elseif (empty($tren['tahun']))
        <div class="rounded-md border border-kurang bg-kurang-bg p-6">
            <p class="font-semibold text-kurang">Data belum tersedia</p>
            <p class="mt-1 text-[13px] text-teks-700">Belum ada berkas Rapor Pendidikan untuk kombinasi ini.</p>
        </div>
    @elseif (! $tren['cukup_tahun'])
        <div class="rounded-md border border-krem-300 bg-kartu p-6">
            <p class="font-semibold text-teks-900">Tren memerlukan minimal dua edisi</p>
            <p class="mt-1 text-[13px] text-teks-700">
                Analisis tren memerlukan minimal dua edisi Rapor Pendidikan. Saat ini baru tersedia
                {{ count($tren['tahun']) }} edisi ({{ implode(', ', $tren['tahun']) }}).
            </p>
        </div>
    @else
        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div class="rounded-md border border-krem-300 bg-kartu p-4">
                <div class="tabular text-[32px] font-bold leading-none text-kurang">{{ $tren['ringkasan']['memburuk_berturut'] }}</div>
                <div class="mt-1 text-xs font-medium text-teks-700">Memburuk 2 tahun berturut-turut</div>
            </div>
            <div class="rounded-md border border-krem-300 bg-kartu p-4">
                <div class="tabular text-[32px] font-bold leading-none text-baik">{{ $tren['ringkasan']['membaik_konsisten'] }}</div>
                <div class="mt-1 text-xs font-medium text-teks-700">Membaik konsisten</div>
            </div>
            <div class="rounded-md border border-krem-300 bg-kartu p-4">
                <div class="tabular text-[32px] font-bold leading-none text-kosong">{{ $tren['ringkasan']['stabil'] }}</div>
                <div class="mt-1 text-xs font-medium text-teks-700">Relatif stabil</div>
            </div>
        </div>

        <p class="text-[13px] text-teks-500">
            {{ $tren['wilayah']['nama'] }} · {{ $tren['jenis_satuan'] }} · {{ $tren['status_satuan'] }} ·
            edisi {{ implode(', ', $tren['tahun']) }}
        </p>

        {{-- Grafik garis: satu garis per indikator, sumbu Y tiga tingkat (DESIGN.md 5) --}}
        <div class="rounded-md border border-krem-300 bg-kartu p-5">
            <h2 class="text-[15px] font-semibold text-teks-900">Lintasan capaian</h2>
            <div
                wire:key="grafik-tren-{{ md5(json_encode($tren['grafik'])) }}"
                wire:ignore
                x-data="trenChart(@js($tren['grafik']))"
                x-init="gambar()"
                class="mt-3"
            >
                <div class="h-72"><canvas x-ref="kanvas"></canvas></div>
                @if (empty($tren['grafik']['seri']))
                    <p class="text-[13px] text-teks-500">Belum ada indikator dengan data yang cukup untuk digambar.</p>
                @endif
            </div>
        </div>

        {{-- Indikator memburuk berturut: paling penting --}}
        @if (! empty($tren['memburuk']))
            <div class="overflow-hidden rounded-md border border-kurang bg-kartu">
                <div class="border-b border-krem-300 bg-kurang-bg px-5 py-3">
                    <h2 class="text-[15px] font-semibold text-kurang">Memburuk dua tahun berturut-turut</h2>
                </div>
                <div class="flex flex-col divide-y divide-krem-300">
                    @foreach ($tren['memburuk'] as $ind)
                        <div class="px-5 py-3">
                            <div class="text-[13px] font-medium text-teks-900">{{ $ind['nomor'] }} {{ $ind['nama'] }}</div>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                @foreach ($ind['deret'] as $t => $label)
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[11px] font-medium text-teks-500 tabular">{{ $t }}</span>
                                        <x-badge-capaian :label="$label" />
                                    </div>
                                    @unless ($loop->last)
                                        <span aria-hidden="true" class="text-teks-300">→</span>
                                    @endunless
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Indikator membaik konsisten: praktik yang layak dipertahankan --}}
        @if (! empty($tren['membaik']))
            <div class="overflow-hidden rounded-md border border-krem-300 bg-kartu">
                <div class="border-b border-krem-300 bg-baik-bg px-5 py-3">
                    <h2 class="text-[15px] font-semibold text-baik">Membaik konsisten — layak dipertahankan</h2>
                </div>
                <div class="flex flex-col divide-y divide-krem-300">
                    @foreach ($tren['membaik'] as $ind)
                        <div class="px-5 py-3">
                            <div class="text-[13px] font-medium text-teks-900">{{ $ind['nomor'] }} {{ $ind['nama'] }}</div>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                @foreach ($ind['deret'] as $t => $label)
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[11px] font-medium text-teks-500 tabular">{{ $t }}</span>
                                        <x-badge-capaian :label="$label" />
                                    </div>
                                    @unless ($loop->last)
                                        <span aria-hidden="true" class="text-teks-300">→</span>
                                    @endunless
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Seluruh indikator per dimensi --}}
        @foreach ($tren['dimensi'] as $kode => $dim)
            <div class="overflow-hidden rounded-md border border-krem-300 bg-kartu">
                <div class="border-b border-krem-300 bg-krem-200 px-5 py-3">
                    <h2 class="text-[15px] font-semibold text-teks-900">{{ $kode }}. {{ $dim['nama'] }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-[13px]">
                        <thead>
                            <tr class="border-b border-krem-300 text-left text-xs font-semibold uppercase text-teks-700">
                                <th scope="col" class="px-5 py-2">Indikator</th>
                                @foreach ($tren['tahun'] as $t)
                                    <th scope="col" class="px-3 py-2 text-center tabular">{{ $t }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dim['indikator'] as $ind)
                                <tr class="border-b border-krem-300 last:border-0 hover:bg-krem-200">
                                    <td class="px-5 py-2.5">
                                        <span class="font-medium text-teks-900">{{ $ind['nomor'] }}</span>
                                        <span class="text-teks-700"> {{ $ind['nama'] }}</span>
                                    </td>
                                    @foreach ($ind['deret'] as $label)
                                        <td class="px-3 py-2.5 text-center"><x-badge-capaian :label="$label" /></td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>

@assets
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
@endassets

@script
<script>
    Alpine.data('trenChart', (grafik) => ({
        chart: null,

        gambar() {
            if (typeof Chart === 'undefined' || ! grafik.seri || grafik.seri.length === 0) {
                return
            }

            const tingkat = { 1: 'Kurang', 2: 'Sedang', 3: 'Baik' }

            this.chart = new Chart(this.$refs.kanvas, {
                type: 'line',
                data: {
                    labels: grafik.tahun,
                    datasets: grafik.seri.map((s) => ({
                        label: s.nomor + ' ' + s.nama,
                        data: s.nilai,
                        borderColor: s.warna,
                        backgroundColor: s.warna,
                        spanGaps: false,
                        tension: 0,
                        pointRadius: 4,
                    })),
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 200 },
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
                    scales: {
                        y: {
                            min: 1,
                            max: 3,
                            ticks: { stepSize: 1, callback: (v) => tingkat[v] ?? '' },
                            grid: { color: '#DDD5C2' },
                        },
                        x: { grid: { color: '#DDD5C2' } },
                    },
                },
            })
        },

        destroy() {
            this.chart?.destroy()
        },
    }))
</script>
@endscript

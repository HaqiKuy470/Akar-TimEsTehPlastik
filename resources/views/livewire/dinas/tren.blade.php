<div class="flex flex-col gap-6">
    <x-kepala-halaman
        judul="Tren lintas tahun"
        lead="Pergerakan label capaian dari edisi ke edisi. Portal resmi menampilkan capaian satu tahun; di sini terlihat lintasannya, termasuk indikator yang memburuk dua tahun berturut-turut." />

    <x-kartu rapat>
        <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4">
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
    </x-kartu>

    @php $tren = $this->tren; @endphp

    @if ($tren === null)
        <x-kartu rapat>
            <x-kosong ikon="filter" judul="Lengkapi pilihan di atas"
                      pesan="Pilih wilayah, jenjang, dan status satuan pendidikan untuk menampilkan tren." />
        </x-kartu>
    @elseif (empty($tren['tahun']))
        <x-kartu>
            <p class="text-[14px] font-semibold text-kurang">Data belum tersedia</p>
            <p class="mt-1 text-[13px] text-teks-700">Belum ada berkas Rapor Pendidikan untuk kombinasi ini.</p>
        </x-kartu>
    @elseif (! $tren['cukup_tahun'])
        <x-kartu rapat>
            <x-kosong ikon="grafik" judul="Tren memerlukan minimal dua edisi"
                      pesan="Analisis tren memerlukan minimal dua edisi Rapor Pendidikan. Saat ini baru tersedia {{ count($tren['tahun']) }} edisi ({{ implode(', ', $tren['tahun']) }})." />
        </x-kartu>
    @else
        <x-ringkasan-angka :item="[
            ['angka' => $tren['ringkasan']['memburuk_berturut'], 'label' => 'Memburuk 2 tahun berturut-turut', 'warna' => 'kurang'],
            ['angka' => $tren['ringkasan']['membaik_konsisten'], 'label' => 'Membaik konsisten', 'warna' => 'baik'],
            ['angka' => $tren['ringkasan']['stabil'], 'label' => 'Relatif stabil', 'warna' => 'kosong'],
        ]" />

        <p class="-mt-2 text-[12px] text-teks-500">
            {{ $tren['wilayah']['nama'] }} · {{ $tren['jenis_satuan'] }} · {{ $tren['status_satuan'] }} ·
            edisi {{ implode(', ', $tren['tahun']) }}
        </p>

        <x-kartu judul="Lintasan capaian">
            <div
                wire:key="grafik-tren-{{ md5(json_encode($tren['grafik'])) }}"
                wire:ignore
                x-data="trenChart(@js($tren['grafik']))"
                x-init="gambar()"
            >
                <div class="h-72"><canvas x-ref="kanvas"></canvas></div>
                @if (empty($tren['grafik']['seri']))
                    <p class="text-[13px] text-teks-500">Belum ada indikator dengan data yang cukup untuk digambar.</p>
                @endif
            </div>
        </x-kartu>

        @if (! empty($tren['memburuk']))
            <div class="flex flex-col gap-3">
                <x-judul-bagian judul="Memburuk dua tahun berturut-turut" :jumlah="count($tren['memburuk']).' indikator'" />
                <x-kartu rapat>
                    <div class="flex flex-col divide-y divide-krem-300">
                        @foreach ($tren['memburuk'] as $ind)
                            <div class="px-5 py-3.5">
                                <div class="text-[13px] font-medium text-teks-900">{{ $ind['nomor'] }} {{ $ind['nama'] }}</div>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    @foreach ($ind['deret'] as $t => $label)
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="tabular text-[11px] font-medium text-teks-500">{{ $t }}</span>
                                            <x-badge-capaian :label="$label" />
                                        </span>
                                        @unless ($loop->last)<span aria-hidden="true" class="text-teks-300">→</span>@endunless
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-kartu>
            </div>
        @endif

        @if (! empty($tren['membaik']))
            <div class="flex flex-col gap-3">
                <x-judul-bagian judul="Membaik konsisten — layak dipertahankan" :jumlah="count($tren['membaik']).' indikator'" />
                <x-kartu rapat>
                    <div class="flex flex-col divide-y divide-krem-300">
                        @foreach ($tren['membaik'] as $ind)
                            <div class="px-5 py-3.5">
                                <div class="text-[13px] font-medium text-teks-900">{{ $ind['nomor'] }} {{ $ind['nama'] }}</div>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    @foreach ($ind['deret'] as $t => $label)
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="tabular text-[11px] font-medium text-teks-500">{{ $t }}</span>
                                            <x-badge-capaian :label="$label" />
                                        </span>
                                        @unless ($loop->last)<span aria-hidden="true" class="text-teks-300">→</span>@endunless
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-kartu>
            </div>
        @endif

        @foreach ($tren['dimensi'] as $kode => $dim)
            <div class="flex flex-col gap-3">
                <x-judul-bagian :judul="$kode.'. '.$dim['nama']" :jumlah="count($dim['indikator']).' indikator'" />
                <x-kartu rapat>
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px]">
                            <thead>
                                <tr class="border-b border-krem-300 text-left">
                                    <th scope="col" class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Indikator</th>
                                    @foreach ($tren['tahun'] as $t)
                                        <th scope="col" class="tabular px-3 py-2.5 text-center text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">{{ $t }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dim['indikator'] as $ind)
                                    <tr class="border-b border-krem-300 last:border-0 hover:bg-krem-150">
                                        <td class="px-4 py-3">
                                            <span class="font-medium text-teks-900">{{ $ind['nomor'] }}</span>
                                            <span class="text-teks-700"> {{ $ind['nama'] }}</span>
                                        </td>
                                        @foreach ($ind['deret'] as $label)
                                            <td class="px-3 py-3 text-center"><x-badge-capaian :label="$label" /></td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-kartu>
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
                    datasets: grafik.seri.map((s) => {
                        const bergerak = s.klasifikasi !== 'stabil'
                        return {
                            label: s.nomor + ' ' + s.nama,
                            data: s.nilai,
                            borderColor: s.warna,
                            backgroundColor: s.warna,
                            borderWidth: bergerak ? 2.5 : 1.5,
                            borderDash: s.klasifikasi === 'stabil' ? [4, 4] : [],
                            spanGaps: false,
                            tension: 0,
                            pointRadius: bergerak ? 4 : 3,
                            pointHoverRadius: 6,
                        }
                    }),
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 200 },
                    interaction: { mode: 'nearest', intersect: false },
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                        tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${tingkat[c.raw] ?? 'Tidak tersedia'}` } },
                    },
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

<div class="flex flex-col gap-6">
    <div>
        <h1 class="text-2xl font-bold text-teks-900">Profil capaian sekolah</h1>
        <p class="mt-1 max-w-3xl text-teks-700">
            Kondisi seluruh indikator mutu pendidikan sekolah Anda, dikelompokkan menurut dimensinya.
        </p>
    </div>

    @if ($this->sekolah === null)
        <div class="rounded-md border border-krem-300 bg-kartu p-10 text-center">
            <p class="text-teks-700">Belum ada berkas Rapor Pendidikan yang diunggah.</p>
            <a href="{{ route('sekolah.unggah') }}" class="mt-2 inline-block text-[13px] font-semibold text-biru-700 underline">Unggah berkas sekolah</a>
        </div>
    @else
        {{-- Pemilih jenjang/status hanya bila berkas memuat lebih dari satu --}}
        @if ($this->jenjangTersedia->count() > 1 || $this->statusTersedia->count() > 1)
            <div class="rounded-md border border-krem-300 bg-kartu p-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <label class="flex flex-col gap-1 text-xs font-medium text-teks-700">
                        Jenjang
                        <select wire:model.live="jenisSatuan" class="h-9 rounded border border-krem-300 bg-white px-2 text-[13px] text-teks-900">
                            @foreach ($this->jenjangTersedia as $j)
                                <option value="{{ $j }}">{{ $j }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="flex flex-col gap-1 text-xs font-medium text-teks-700">
                        Status satuan
                        <select wire:model.live="statusSatuan" class="h-9 rounded border border-krem-300 bg-white px-2 text-[13px] text-teks-900">
                            @foreach ($this->statusTersedia as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>
        @endif

        @php $profil = $this->profil; @endphp

        @if ($profil === null || ! $profil['tersedia'])
            <div class="rounded-md border border-kurang bg-kurang-bg p-6">
                <p class="font-semibold text-kurang">Data belum tersedia</p>
                <p class="mt-1 text-[13px] text-teks-700">
                    Berkas sekolah tidak memuat indikator yang dapat ditampilkan untuk kombinasi ini.
                </p>
            </div>
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ([
                    ['Perlu perhatian', 'merah', 'text-kurang'],
                    ['Cukup', 'kuning', 'text-sedang'],
                    ['Baik', 'hijau', 'text-baik'],
                    ['Tidak tersedia', 'tidak_tersedia', 'text-kosong'],
                ] as [$judul, $kunci, $kelasWarna])
                    <div class="rounded-md border border-krem-300 bg-kartu p-4">
                        <div class="tabular text-[32px] font-bold leading-none {{ $kelasWarna }}">{{ $profil['ringkasan'][$kunci] }}</div>
                        <div class="mt-1 text-xs font-medium text-teks-700">{{ $judul }}</div>
                    </div>
                @endforeach
            </div>

            <p class="text-[13px] text-teks-500">
                {{ $profil['wilayah']['nama'] }} · {{ $profil['jenis_satuan'] }} ·
                {{ $profil['status_satuan'] }} · Data {{ $profil['tahun'] }} ·
                {{ $profil['ringkasan']['total'] }} indikator diukur
            </p>

            @foreach ($profil['dimensi'] as $kode => $dim)
                <div class="overflow-hidden rounded-md border border-krem-300 bg-kartu">
                    <div class="border-b border-krem-300 bg-krem-200 px-5 py-3">
                        <h2 class="text-[15px] font-semibold text-teks-900">{{ $kode }}. {{ $dim['nama'] }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px]">
                            <thead>
                                <tr class="border-b border-krem-300 text-left text-xs font-semibold uppercase text-teks-700">
                                    <th scope="col" class="px-5 py-2">Indikator</th>
                                    <th scope="col" class="px-5 py-2">Capaian</th>
                                    <th scope="col" class="px-5 py-2">Perubahan</th>
                                    <th scope="col" class="px-5 py-2">Ambang perlu perhatian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dim['indikator'] as $ind)
                                    <tr class="border-b border-krem-300 last:border-0 hover:bg-krem-200">
                                        <td class="px-5 py-2.5">
                                            <span class="font-medium text-teks-900">{{ $ind['nomor'] }}</span>
                                            <span class="text-teks-700"> {{ $ind['nama'] }}</span>
                                        </td>
                                        <td class="px-5 py-2.5"><x-badge-capaian :label="$ind['label_capaian']" /></td>
                                        <td class="px-5 py-2.5"><x-arah-perubahan :nilai="$ind['perubahan_nilai']" /></td>
                                        <td class="max-w-md px-5 py-2.5 text-teks-500">{{ $ind['ambang']['merah'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            @if (! empty($profil['tidak_tersedia']))
                <div class="rounded-md border border-krem-300 bg-kartu p-5">
                    <h2 class="text-[15px] font-semibold text-teks-900">Indikator tanpa data</h2>
                    <p class="mt-1 text-[13px] text-teks-500">
                        {{ count($profil['tidak_tersedia']) }} indikator tidak diukur pada berkas sekolah ini.
                        Ini bukan nilai nol, melainкан ketiadaan data pada berkas sumber.
                    </p>
                    <ul class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-[13px] text-teks-700">
                        @foreach ($profil['tidak_tersedia'] as $ind)
                            <li><span class="font-medium">{{ $ind['nomor'] }}</span> {{ $ind['nama'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif
    @endif
</div>

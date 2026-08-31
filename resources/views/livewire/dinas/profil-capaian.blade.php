<div class="flex flex-col gap-6">
    <div>
        <h1 class="text-2xl font-bold text-teks-900">Profil capaian daerah</h1>
        <p class="mt-1 max-w-3xl text-teks-700">
            Kondisi seluruh indikator mutu pendidikan satu kabupaten/kota pada satu jenjang,
            dikelompokkan menurut dimensinya.
        </p>
    </div>

    {{-- Pemilih --}}
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
    </div>

    @php $profil = $this->profil; @endphp

    @if ($profil === null)
        <div class="rounded-md border border-krem-300 bg-kartu p-10 text-center">
            <p class="text-teks-700">Lengkapi pilihan di atas untuk menampilkan profil capaian.</p>
            <p class="mt-1 text-[13px] text-teks-500">Pilih provinsi, kabupaten/kota, jenjang, dan status satuan pendidikan.</p>
        </div>
    @elseif (! $profil['tersedia'])
        <div class="rounded-md border border-kurang bg-kurang-bg p-6">
            <p class="font-semibold text-kurang">Data belum tersedia</p>
            <p class="mt-1 text-[13px] text-teks-700">
                Belum ada berkas Rapor Pendidikan tahun {{ $profil['tahun'] }} yang selesai diimpor,
                atau kombinasi jenjang dan status ini tidak ada di berkas.
            </p>
        </div>
    @else
        {{-- Ringkasan --}}
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
            {{ $profil['status_satuan'] }} · {{ $profil['ringkasan']['total'] }} indikator diukur
        </p>

        {{-- Kelompok dimensi --}}
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

        {{-- Indikator tidak tersedia, dibedakan tegas dari nilai nol (DESIGN.md 6) --}}
        @if (! empty($profil['tidak_tersedia']))
            <div class="rounded-md border border-krem-300 bg-kartu p-5">
                <h2 class="text-[15px] font-semibold text-teks-900">Indikator tanpa data di level kabupaten/kota</h2>
                <p class="mt-1 text-[13px] text-teks-500">
                    {{ count($profil['tidak_tersedia']) }} indikator tidak diukur untuk kombinasi ini.
                    Ini bukan nilai nol, melainkan ketiadaan data pada berkas sumber.
                </p>
                <ul class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-[13px] text-teks-700">
                    @foreach ($profil['tidak_tersedia'] as $ind)
                        <li><span class="font-medium">{{ $ind['nomor'] }}</span> {{ $ind['nama'] }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif
</div>

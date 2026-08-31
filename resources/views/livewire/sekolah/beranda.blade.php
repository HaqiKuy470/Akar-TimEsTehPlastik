<div class="flex flex-col gap-6">
    @php $sekolah = $this->sekolah; @endphp

    <div>
        <h1 class="text-2xl font-bold text-teks-900">Beranda sekolah</h1>
        <p class="mt-1 max-w-3xl text-teks-700">
            Ruang kerja kepala sekolah dan tim untuk menerjemahkan Rapor Pendidikan sekolah
            menjadi Rencana Kerja Tahunan berbasis data.
        </p>
    </div>

    @if ($sekolah === null)
        {{-- Belum ada berkas: ajak unggah --}}
        <div class="rounded-md border border-krem-300 bg-kartu p-8">
            <h2 class="text-[15px] font-semibold text-teks-900">Mulai dengan mengunggah Rapor Pendidikan sekolah</h2>
            <p class="mt-2 max-w-2xl text-[13px] text-teks-700">
                Unduh berkas Rapor Pendidikan satuan pendidikan dari akun belajar.id sekolah Anda,
                lalu unggah di sini. Sistem akan menyusun profil capaian, prioritas masalah, akar
                masalah, dan draf rencana kerja secara otomatis.
            </p>
            <a href="{{ route('sekolah.unggah') }}"
               class="mt-4 inline-block h-9 rounded bg-biru-700 px-4 text-[13px] font-semibold leading-9 text-white hover:bg-biru-600">
                Unggah berkas
            </a>
        </div>
    @else
        <div class="rounded-md border border-krem-300 bg-kartu p-5">
            <p class="text-xs font-medium uppercase text-teks-500">Sekolah</p>
            <p class="mt-0.5 text-[18px] font-semibold text-teks-900">{{ $sekolah->nama_satuan }}</p>
            <p class="mt-1 text-[13px] text-teks-500">
                @if ($sekolah->kabupaten_kota){{ $sekolah->kabupaten_kota }} · @endif
                @if ($sekolah->provinsi){{ $sekolah->provinsi }} · @endif
                @if ($this->kombinasi)Data {{ $this->kombinasi['tahun'] }} · {{ $this->kombinasi['jenis_satuan'] }}@endif
            </p>
            @if ($this->impor)
                <p class="mt-1 text-[12px] text-teks-500">
                    Berkas terakhir: {{ $this->impor->nama_berkas }} —
                    {{ optional($this->impor->diproses_pada)->translatedFormat('d F Y') ?? 'selesai' }}.
                    <a href="{{ route('sekolah.unggah') }}" class="text-biru-700 underline">Unggah berkas baru</a>
                </p>
            @endif
        </div>

        {{-- Ringkasan capaian --}}
        @if ($this->ringkasan)
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ([
                    ['Perlu perhatian', 'merah', 'text-kurang'],
                    ['Cukup', 'kuning', 'text-sedang'],
                    ['Baik', 'hijau', 'text-baik'],
                    ['Tidak tersedia', 'tidak_tersedia', 'text-kosong'],
                ] as [$judul, $kunci, $kelasWarna])
                    <div class="rounded-md border border-krem-300 bg-kartu p-4">
                        <div class="tabular text-[32px] font-bold leading-none {{ $kelasWarna }}">{{ $this->ringkasan[$kunci] }}</div>
                        <div class="mt-1 text-xs font-medium text-teks-700">{{ $judul }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-md border border-sedang bg-sedang-bg p-4 text-[13px] text-teks-700">
                Berkas sudah diunggah, tetapi belum ada indikator yang dapat dianalisis untuk kombinasi
                jenjang dan status pada berkas ini.
            </div>
        @endif

        {{-- Pintasan --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ([
                ['sekolah.profil', 'Profil capaian', 'Seluruh indikator sekolah, dikelompokkan menurut dimensi.'],
                ['sekolah.prioritas', 'Prioritas & akar masalah', 'Indikator paling mendesak beserta dugaan penyebabnya.'],
                ['sekolah.rkt', 'Rencana Kerja Tahunan', 'Draf RKT berbasis data, siap disunting dan diunduh.'],
            ] as [$rute, $judul, $ket])
                <a href="{{ route($rute) }}"
                   class="rounded-md border border-krem-300 bg-kartu p-5 hover:border-biru-700">
                    <p class="text-[15px] font-semibold text-teks-900">{{ $judul }}</p>
                    <p class="mt-1 text-[13px] text-teks-700">{{ $ket }}</p>
                </a>
            @endforeach
        </div>
    @endif
</div>

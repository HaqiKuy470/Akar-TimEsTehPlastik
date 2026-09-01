<div class="flex flex-col gap-6">
    @php
        $sekolah = $this->sekolah;
        $kombinasi = $this->kombinasi;
        $ringkasan = $this->ringkasan;
    @endphp

    @if ($sekolah === null)
        <x-kepala-halaman
            judul="Beranda sekolah"
            lead="Ruang kerja kepala sekolah dan tim kurikulum untuk menerjemahkan Rapor Pendidikan sekolah menjadi Rencana Kerja Tahunan berbasis data.">
            <x-slot:aksi><x-tautan-panduan anchor="alur" /></x-slot:aksi>
        </x-kepala-halaman>

        <x-kartu rapat>
            <x-kosong
                ikon="unggah"
                judul="Mulai dengan mengunggah Rapor Pendidikan sekolah"
                pesan="Unduh berkas Rapor Pendidikan satuan pendidikan dari akun belajar.id sekolah Anda, lalu unggah di sini. Sistem menyusun profil capaian, prioritas, akar masalah, dan draf rencana kerja secara otomatis.">
                <x-slot:aksi>
                    <x-tombol jenis="primer" :href="route('sekolah.unggah')">Unggah berkas</x-tombol>
                </x-slot:aksi>
            </x-kosong>
        </x-kartu>
    @else
        <x-kepala-halaman :judul="$sekolah->nama_satuan">
            <x-slot:konteks>
                @if ($sekolah->kabupaten_kota){{ $sekolah->kabupaten_kota }} · @endif
                @if ($sekolah->provinsi){{ $sekolah->provinsi }}@endif
                @if ($kombinasi) · {{ $kombinasi['jenis_satuan'] }} · edisi {{ $kombinasi['tahun'] }}@endif
                @if ($this->impor?->diproses_pada) · diunggah {{ $this->impor->diproses_pada->translatedFormat('d F Y') }}@endif
            </x-slot:konteks>
            <x-slot:aksi>
                <div class="flex items-center gap-2">
                    <x-tautan-panduan anchor="alur" />
                    <x-tombol jenis="sekunder" ukuran="kecil" :href="route('sekolah.unggah')">Unggah berkas baru</x-tombol>
                </div>
            </x-slot:aksi>
        </x-kepala-halaman>

        @if ($ringkasan)
            <x-ringkasan-angka :item="[
                ['angka' => $ringkasan['merah'], 'label' => 'Perlu perhatian', 'warna' => 'kurang'],
                ['angka' => $ringkasan['kuning'], 'label' => 'Cukup', 'warna' => 'sedang'],
                ['angka' => $ringkasan['hijau'], 'label' => 'Baik', 'warna' => 'baik'],
                ['angka' => $ringkasan['tidak_tersedia'], 'label' => 'Tidak tersedia', 'warna' => 'kosong'],
            ]" />
        @else
            <x-kartu>
                <p class="text-[13px] text-teks-700">
                    Berkas sudah diunggah, tetapi belum ada indikator yang dapat dianalisis
                    untuk kombinasi jenjang dan status pada berkas ini.
                </p>
            </x-kartu>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['sekolah.profil', 'Profil capaian', 'Seluruh indikator sekolah, dikelompokkan menurut dimensi.'],
                ['sekolah.prioritas', 'Prioritas & akar masalah', 'Indikator paling mendesak beserta dugaan penyebabnya.'],
                ['sekolah.rkt', 'Rencana Kerja Tahunan', 'Draf RKT berbasis data, siap disunting dan diunduh.'],
            ] as [$rute, $judul, $ket])
                <x-kartu>
                    <div class="flex h-full flex-col">
                        <h3 class="text-[14px] font-semibold text-teks-900">{{ $judul }}</h3>
                        <p class="mt-1 flex-1 text-[13px] text-teks-700">{{ $ket }}</p>
                        <div class="mt-3">
                            <x-tombol jenis="tersier" :href="route($rute)">Buka →</x-tombol>
                        </div>
                    </div>
                </x-kartu>
            @endforeach
        </div>
    @endif
</div>

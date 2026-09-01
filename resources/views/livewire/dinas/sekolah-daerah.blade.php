<div class="flex flex-col gap-6">
    <x-kepala-halaman
        judul="Sekolah di wilayah"
        lead="Capaian sekolah yang berkas Rapor Pendidikannya sudah diunggah oleh kepala sekolahnya. Ditampilkan baca-saja sebagai konteks atas data agregat kabupaten/kota; ruang kerja sekolah tetap terpisah.">
        <x-slot:aksi>
            <div class="flex items-center gap-2">
                <x-tautan-panduan anchor="profil" />
                @if ($this->kabupaten)
                    <x-tombol jenis="sekunder" ukuran="kecil"
                        :href="route('dinas.profil', ['provinsi' => $this->kabupaten->provinsi, 'wilayahId' => $this->kabupaten->id])">
                        Profil {{ $this->kabupaten->kabupaten_kota }}
                    </x-tombol>
                @endif
            </div>
        </x-slot:aksi>
    </x-kepala-halaman>

    @if ($this->kabkota === null)
        <x-kartu rapat>
            <x-kosong ikon="filter" judul="Belum ada wilayah dipilih"
                pesan="Buka halaman Profil capaian, pilih sebuah kabupaten/kota, lalu ikuti tautan sekolah yang muncul di sana." />
        </x-kartu>
    @elseif ($this->daftar->isEmpty())
        <x-kartu rapat>
            <x-kosong ikon="unggah"
                judul="Belum ada sekolah yang mengunggah berkas di {{ optional($this->kabupaten)->kabupaten_kota ?? 'wilayah ini' }}"
                pesan="Sekolah muncul di sini setelah kepala sekolahnya mengunggah Rapor Pendidikan satuan pendidikan melalui mode satuan pendidikan." />
        </x-kartu>
    @else
        <div class="grid gap-6 lg:grid-cols-[300px_1fr]">
            <div class="flex flex-col gap-3">
                <x-judul-bagian :judul="'Sekolah di '.optional($this->kabupaten)->kabupaten_kota" :jumlah="$this->daftar->count().' sekolah'" />
                <div class="flex flex-col gap-2">
                    @foreach ($this->daftar as $s)
                        <a href="{{ route('dinas.sekolah', ['kabkota' => $this->kabkota, 'wilayah' => $s['wilayah_id']]) }}"
                           wire:navigate
                           @class([
                               'flex flex-col gap-1.5 rounded-md border p-3 text-left',
                               'border-biru-700 bg-biru-100' => $wilayah === $s['wilayah_id'],
                               'border-krem-300 bg-kartu hover:border-teks-400' => $wilayah !== $s['wilayah_id'],
                           ])>
                            <span class="text-[13px] font-semibold text-teks-900">{{ $s['nama'] }}</span>
                            <span class="text-[11px] text-teks-500">{{ $s['jenis_satuan'] }} · {{ $s['status_satuan'] }} · {{ $s['tahun'] }}</span>
                            <span class="mt-0.5 flex gap-3 text-[11px] tabular">
                                <span class="text-kurang">{{ $s['merah'] }} kurang</span>
                                <span class="text-sedang">{{ $s['kuning'] }} sedang</span>
                                <span class="text-baik">{{ $s['hijau'] }} baik</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="flex flex-col gap-5">
                @php $profil = $this->profil; @endphp

                @if ($this->sekolah === null)
                    <x-kartu rapat>
                        <x-kosong ikon="dokumen" judul="Pilih satu sekolah"
                            pesan="Klik nama sekolah di sebelah kiri untuk melihat capaian indikatornya." />
                    </x-kartu>
                @elseif ($profil === null || ! $profil['tersedia'])
                    <x-kartu>
                        <p class="text-[14px] font-semibold text-teks-900">{{ $this->sekolah->nama_satuan }}</p>
                        <p class="mt-1 text-[13px] text-teks-500">Capaian sekolah ini tidak dapat ditampilkan.</p>
                    </x-kartu>
                @else
                    <x-kepala-halaman :judul="$profil['wilayah']['nama']">
                        <x-slot:konteks>
                            {{ $profil['jenis_satuan'] }} · {{ $profil['status_satuan'] }} · Data {{ $profil['tahun'] }} ·
                            {{ $profil['ringkasan']['total'] }} indikator · dibaca dari sisi dinas
                        </x-slot:konteks>
                    </x-kepala-halaman>

                    <x-ringkasan-angka :item="[
                        ['angka' => $profil['ringkasan']['merah'], 'label' => 'Perlu perhatian', 'warna' => 'kurang'],
                        ['angka' => $profil['ringkasan']['kuning'], 'label' => 'Cukup', 'warna' => 'sedang'],
                        ['angka' => $profil['ringkasan']['hijau'], 'label' => 'Baik', 'warna' => 'baik'],
                        ['angka' => $profil['ringkasan']['tidak_tersedia'], 'label' => 'Tidak tersedia', 'warna' => 'kosong'],
                    ]" />

                    @if (! empty($profil['dimensi_grafik']))
                        <x-kartu>
                            <x-grafik-komposisi :dimensi="$profil['dimensi_grafik']" judul="Sebaran per dimensi" />
                        </x-kartu>
                    @endif

                    @foreach ($profil['dimensi'] as $kode => $dim)
                        <div class="flex flex-col gap-3">
                            <x-judul-bagian :judul="$kode.'. '.$dim['nama']" :jumlah="count($dim['indikator']).' indikator'" />
                            <x-kartu rapat>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-[13px]">
                                        <thead>
                                            <tr class="border-b border-krem-300 text-left">
                                                <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Indikator</th>
                                                <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Capaian</th>
                                                <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Perubahan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($dim['indikator'] as $ind)
                                                <tr class="border-b border-krem-300 last:border-0 hover:bg-krem-150">
                                                    <td class="px-4 py-3">
                                                        <span class="font-medium text-teks-900">{{ $ind['nomor'] }}</span>
                                                        <span class="text-teks-700"> {{ $ind['nama'] }}</span>
                                                    </td>
                                                    <td class="px-4 py-3"><x-badge-capaian :label="$ind['label_capaian']" /></td>
                                                    <td class="px-4 py-3"><x-arah-perubahan :nilai="$ind['perubahan_nilai']" /></td>
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
        </div>
    @endif

    <x-grafik-skrip />
</div>

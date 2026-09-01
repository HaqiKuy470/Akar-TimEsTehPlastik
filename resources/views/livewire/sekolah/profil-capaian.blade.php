<div class="flex flex-col gap-6">
    <x-kepala-halaman
        judul="Profil capaian sekolah"
        lead="Kondisi seluruh indikator mutu pendidikan sekolah Anda, dikelompokkan menurut dimensinya." />

    @if ($this->sekolah === null)
        <x-kartu rapat>
            <x-kosong
                ikon="unggah"
                judul="Belum ada berkas Rapor Pendidikan sekolah"
                pesan="Unggah berkas dari akun belajar.id sekolah Anda untuk menampilkan profil capaian.">
                <x-slot:aksi>
                    <x-tombol jenis="primer" :href="route('sekolah.unggah')">Unggah berkas</x-tombol>
                </x-slot:aksi>
            </x-kosong>
        </x-kartu>
    @else
        @if ($this->jenjangTersedia->count() > 1 || $this->statusTersedia->count() > 1)
            <x-kartu rapat>
                <div class="grid gap-4 p-4 sm:grid-cols-2">
                    <x-pilih label="Jenjang" wire:model.live="jenisSatuan">
                        @foreach ($this->jenjangTersedia as $j)
                            <option value="{{ $j }}">{{ $j }}</option>
                        @endforeach
                    </x-pilih>
                    <x-pilih label="Status satuan" wire:model.live="statusSatuan">
                        @foreach ($this->statusTersedia as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </x-pilih>
                </div>
            </x-kartu>
        @endif

        @php $profil = $this->profil; @endphp

        <div wire:loading.delay.flex wire:target="jenisSatuan,statusSatuan" class="flex-col gap-3">
            <div class="rangka-muat h-16"></div>
            <div class="rangka-muat h-64"></div>
        </div>

        <div wire:loading.remove wire:target="jenisSatuan,statusSatuan" class="flex flex-col gap-6">
            @if ($profil === null || ! $profil['tersedia'])
                <x-kartu>
                    <p class="text-[14px] font-semibold text-kurang">Data belum tersedia</p>
                    <p class="mt-1 text-[13px] text-teks-700">
                        Berkas sekolah tidak memuat indikator yang dapat ditampilkan untuk kombinasi ini.
                    </p>
                </x-kartu>
            @else
                <x-ringkasan-angka :item="[
                    ['angka' => $profil['ringkasan']['merah'], 'label' => 'Perlu perhatian', 'warna' => 'kurang'],
                    ['angka' => $profil['ringkasan']['kuning'], 'label' => 'Cukup', 'warna' => 'sedang'],
                    ['angka' => $profil['ringkasan']['hijau'], 'label' => 'Baik', 'warna' => 'baik'],
                    ['angka' => $profil['ringkasan']['tidak_tersedia'], 'label' => 'Tidak tersedia', 'warna' => 'kosong'],
                ]" />

                <p class="-mt-2 text-[12px] text-teks-500">
                    {{ $profil['wilayah']['nama'] }} · {{ $profil['jenis_satuan'] }} ·
                    {{ $profil['status_satuan'] }} · Data {{ $profil['tahun'] }} ·
                    {{ $profil['ringkasan']['total'] }} indikator diukur
                </p>

                @if (! empty($profil['dimensi_grafik']))
                    <x-kartu>
                        <x-grafik-komposisi :dimensi="$profil['dimensi_grafik']"
                            judul="Sebaran per dimensi" />
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
                                            <th scope="col" class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Indikator</th>
                                            <th scope="col" class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Capaian</th>
                                            <th scope="col" class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Perubahan</th>
                                            <th scope="col" class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Ambang perlu perhatian</th>
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
                                                <td class="max-w-sm px-4 py-3 text-[12px] leading-relaxed text-teks-500">{{ $ind['ambang']['merah'] ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </x-kartu>
                    </div>
                @endforeach

                @if (! empty($profil['tidak_tersedia']))
                    <x-kartu judul="Indikator tanpa data pada berkas sekolah"
                             :sub="count($profil['tidak_tersedia']).' indikator tidak diukur pada berkas ini. Ini bukan nilai nol, melainkan ketiadaan data pada berkas sumber.'">
                        <ul class="flex flex-wrap gap-x-6 gap-y-1.5 text-[13px] text-teks-700">
                            @foreach ($profil['tidak_tersedia'] as $ind)
                                <li><span class="font-medium text-teks-900">{{ $ind['nomor'] }}</span> {{ $ind['nama'] }}</li>
                            @endforeach
                        </ul>
                    </x-kartu>
                @endif
            @endif
        </div>
    @endif

    <x-grafik-skrip />
</div>

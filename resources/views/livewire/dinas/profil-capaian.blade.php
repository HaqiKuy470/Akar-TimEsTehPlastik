<div class="flex flex-col gap-6">
    <x-kepala-halaman
        judul="Profil capaian daerah"
        lead="Kondisi seluruh indikator mutu pendidikan satu kabupaten/kota pada satu jenjang, dikelompokkan menurut dimensinya." />

    <x-kartu rapat>
        <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-5">
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
        </div>
    </x-kartu>

    <div wire:loading.delay.flex wire:target="tahun,provinsi,wilayahId,jenisSatuan,statusSatuan" class="flex-col gap-3">
        <div class="rangka-muat h-16"></div>
        <div class="rangka-muat h-64"></div>
    </div>

    <div wire:loading.remove wire:target="tahun,provinsi,wilayahId,jenisSatuan,statusSatuan" class="flex flex-col gap-6">
        @php $profil = $this->profil; @endphp

        @if ($profil === null)
            <x-kartu rapat>
                <x-kosong
                    ikon="filter"
                    judul="Lengkapi pilihan di atas"
                    pesan="Pilih provinsi, kabupaten/kota, jenjang, dan status satuan pendidikan untuk menampilkan profil capaian." />
            </x-kartu>
        @elseif (! $profil['tersedia'])
            <x-kartu>
                <p class="text-[14px] font-semibold text-kurang">Data belum tersedia</p>
                <p class="mt-1 text-[13px] text-teks-700">
                    Belum ada berkas Rapor Pendidikan tahun {{ $profil['tahun'] }} yang selesai diimpor,
                    atau kombinasi jenjang dan status ini tidak ada di berkas.
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
                {{ $profil['status_satuan'] }} · {{ $profil['ringkasan']['total'] }} indikator diukur
            </p>

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
                <x-kartu judul="Indikator tanpa data di level kabupaten/kota"
                         :sub="count($profil['tidak_tersedia']).' indikator tidak diukur untuk kombinasi ini. Ini bukan nilai nol, melainkan ketiadaan data pada berkas sumber.'">
                    <ul class="flex flex-wrap gap-x-6 gap-y-1.5 text-[13px] text-teks-700">
                        @foreach ($profil['tidak_tersedia'] as $ind)
                            <li><span class="font-medium text-teks-900">{{ $ind['nomor'] }}</span> {{ $ind['nama'] }}</li>
                        @endforeach
                    </ul>
                </x-kartu>
            @endif
        @endif
    </div>
</div>

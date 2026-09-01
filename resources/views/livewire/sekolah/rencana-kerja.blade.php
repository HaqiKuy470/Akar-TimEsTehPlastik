<div class="flex flex-col gap-6">
    <x-kepala-halaman
        judul="Rencana Kerja Tahunan (RKT)"
        lead="Draf RKT berbasis data, disusun dari hasil analisis akar masalah sekolah. Setiap butir dapat Anda sunting, tambah, atau hapus sebelum diunduh dan dibawa ke rapat penyusunan program.">
        <x-slot:aksi><x-tautan-panduan anchor="rencana" /></x-slot:aksi>
    </x-kepala-halaman>

    @if ($this->sekolah === null)
        <x-kartu rapat>
            <x-kosong
                ikon="unggah"
                judul="Belum ada berkas Rapor Pendidikan sekolah"
                pesan="Unggah berkas dari akun belajar.id sekolah Anda untuk memulai.">
                <x-slot:aksi>
                    <x-tombol jenis="primer" :href="route('sekolah.unggah')">Unggah berkas</x-tombol>
                </x-slot:aksi>
            </x-kosong>
        </x-kartu>
    @elseif ($this->analisis === null)
        <x-kartu rapat>
            <x-kosong
                ikon="grafik"
                judul="Belum ada analisis untuk sekolah ini"
                pesan="Jalankan analisis pada halaman Prioritas & akar masalah lebih dulu.">
                <x-slot:aksi>
                    <x-tombol jenis="primer" :href="route('sekolah.prioritas')">Buka halaman prioritas</x-tombol>
                </x-slot:aksi>
            </x-kosong>
        </x-kartu>
    @else
        <x-kartu rapat>
            <div class="flex flex-wrap items-center gap-3 p-4">
                <x-tombol jenis="primer" wire:click="susunDraf">
                    {{ $rencanaId ? 'Susun ulang draf' : 'Susun draf' }}
                </x-tombol>
                @if ($rencanaId)
                    <x-tombol jenis="sekunder" wire:click="tambahBaris">Tambah baris</x-tombol>
                    <x-tombol jenis="primer" wire:click="simpan">Simpan</x-tombol>
                @endif
                @if ($tersimpan)
                    <span class="text-[13px] font-medium text-baik">Tersimpan.</span>
                @endif

                <span class="mx-1 h-6 w-px bg-krem-300"></span>
                <x-tombol jenis="sekunder" wire:click="unduhPdf">Unduh PDF</x-tombol>
                <x-tombol jenis="sekunder" wire:click="unduhExcel">Unduh Excel</x-tombol>
            </div>
        </x-kartu>

        @if ($rencanaId === null)
            <x-kartu rapat>
                <x-kosong
                    ikon="dokumen"
                    judul="Belum ada draf RKT untuk analisis ini"
                    pesan="Tekan “Susun draf” untuk menghasilkannya dari hasil analisis akar masalah." />
            </x-kartu>
        @elseif (empty($item))
            <x-kartu>
                <p class="text-[14px] font-semibold text-teks-900">Draf kosong</p>
                <p class="mt-1 text-[13px] text-teks-700">
                    Analisis ini belum menghasilkan akar masalah yang cukup didukung bukti.
                    Anda dapat menambah butir RKT secara manual.
                </p>
            </x-kartu>
        @else
            <x-kartu rapat>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[64rem] text-[13px]">
                        <thead>
                            <tr class="border-b border-krem-300 text-left">
                                @foreach (['Masalah', 'Akar masalah', 'Kegiatan', 'Penanggung jawab', 'Indikator keberhasilan', 'Perkiraan waktu'] as $kol)
                                    <th scope="col" class="px-3 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">{{ $kol }}</th>
                                @endforeach
                                <th scope="col" class="px-3 py-2.5"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $selKelas = 'w-full rounded-md border border-krem-300 bg-kartu px-2 py-1.5 text-[13px] text-teks-900 focus:border-biru-700';
                            @endphp
                            @foreach ($item as $i => $baris)
                                <tr wire:key="baris-{{ $i }}" class="border-b border-krem-300 align-top last:border-0">
                                    <td class="px-3 py-2.5"><textarea rows="3" wire:model="item.{{ $i }}.masalah" class="{{ $selKelas }} min-w-40">{{ $baris['masalah'] ?? '' }}</textarea></td>
                                    <td class="px-3 py-2.5"><textarea rows="3" wire:model="item.{{ $i }}.akar_masalah" class="{{ $selKelas }} min-w-40">{{ $baris['akar_masalah'] ?? '' }}</textarea></td>
                                    <td class="px-3 py-2.5"><textarea rows="3" wire:model="item.{{ $i }}.kegiatan" class="{{ $selKelas }} min-w-56">{{ $baris['kegiatan'] ?? '' }}</textarea></td>
                                    <td class="px-3 py-2.5"><input type="text" wire:model="item.{{ $i }}.penanggung_jawab" value="{{ $baris['penanggung_jawab'] ?? '' }}" class="{{ $selKelas }} min-w-36"></td>
                                    <td class="px-3 py-2.5"><textarea rows="3" wire:model="item.{{ $i }}.indikator_keberhasilan" class="{{ $selKelas }} min-w-48">{{ $baris['indikator_keberhasilan'] ?? '' }}</textarea></td>
                                    <td class="px-3 py-2.5"><input type="text" wire:model="item.{{ $i }}.perkiraan_waktu" value="{{ $baris['perkiraan_waktu'] ?? '' }}" class="{{ $selKelas }} min-w-28"></td>
                                    <td class="px-3 py-2.5">
                                        <x-tombol jenis="merusak" ukuran="kecil" wire:click="hapusBaris({{ $i }})">Hapus</x-tombol>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-kartu>
            <p class="text-[12px] text-teks-500">
                {{ count($item) }} butir. Perubahan baru tersimpan setelah Anda menekan “Simpan”.
            </p>
        @endif
    @endif
</div>

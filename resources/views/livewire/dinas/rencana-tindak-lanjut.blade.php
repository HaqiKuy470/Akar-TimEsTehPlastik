<div class="flex flex-col gap-6">
    <x-kepala-halaman
        judul="Rencana tindak lanjut"
        lead="Draf rencana pembenahan yang disusun dari hasil analisis akar masalah. Setiap butir dapat Anda sunting, tambah, atau hapus sebelum diunduh." />

    <x-kartu rapat>
        <div class="p-4">
            <x-pilih label="Analisis" wire:model.live="analisisId" class="max-w-xl">
                <option value="">— pilih analisis —</option>
                @foreach ($this->analisisTersedia as $opsi)
                    <option value="{{ $opsi['id'] }}">{{ $opsi['label'] }}</option>
                @endforeach
            </x-pilih>

            @if ($this->analisisTersedia->isEmpty())
                <p class="mt-3 text-[13px] text-teks-500">
                    Belum ada analisis. Buka
                    <a href="{{ route('dinas.prioritas') }}" wire:navigate class="text-biru-700 underline underline-offset-2">Prioritas &amp; akar masalah</a>
                    dan jalankan analisis untuk sebuah wilayah lebih dulu.
                </p>
            @endif
        </div>

        @if ($this->analisis !== null)
            <div class="flex flex-wrap items-center gap-3 border-t border-krem-300 px-4 py-3">
                <x-tombol wire:click="susunDraf">{{ $rencanaId ? 'Susun ulang draf' : 'Susun draf' }}</x-tombol>
                @if ($rencanaId)
                    <x-tombol jenis="sekunder" wire:click="tambahBaris">Tambah baris</x-tombol>
                    <x-tombol wire:click="simpan">Simpan</x-tombol>
                @endif
                @if ($tersimpan)
                    <span class="text-[13px] font-medium text-baik">Tersimpan</span>
                @endif
                @if ($rencanaId)
                    <span class="ml-auto flex items-center gap-2">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Unduh</span>
                        <x-tombol jenis="sekunder" ukuran="kecil" wire:click="unduhPdf">PDF</x-tombol>
                        <x-tombol jenis="sekunder" ukuran="kecil" wire:click="unduhExcel">Excel</x-tombol>
                    </span>
                @endif
            </div>
        @endif
    </x-kartu>

    @if ($this->analisis !== null)
        @if ($rencanaId === null)
            <x-kartu rapat>
                <x-kosong ikon="dokumen" judul="Belum ada draf untuk analisis ini"
                          pesan="Tekan “Susun draf” untuk menghasilkannya dari hasil analisis akar masalah." />
            </x-kartu>
        @elseif (empty($item))
            <x-kartu rapat>
                <x-kosong ikon="dokumen" judul="Draf kosong"
                          pesan="Analisis ini belum menghasilkan akar masalah yang cukup didukung bukti. Anda dapat menambah butir rencana secara manual." />
            </x-kartu>
        @else
            <x-kartu rapat>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[64rem] text-[13px]">
                        <thead>
                            <tr class="border-b border-krem-300 bg-krem-200 text-left">
                                @foreach (['Masalah', 'Akar masalah', 'Kegiatan', 'Penanggung jawab', 'Indikator keberhasilan', 'Perkiraan waktu'] as $kol)
                                    <th scope="col" class="px-3 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">{{ $kol }}</th>
                                @endforeach
                                <th scope="col" class="px-3 py-2.5"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($item as $i => $baris)
                                <tr wire:key="baris-{{ $i }}" class="border-b border-krem-300 align-top last:border-0">
                                    @foreach (['masalah' => 'w-40', 'akar_masalah' => 'w-40', 'kegiatan' => 'w-56'] as $kunci => $lebar)
                                        <td class="px-3 py-2">
                                            <textarea rows="3" wire:model="item.{{ $i }}.{{ $kunci }}"
                                                      class="{{ $lebar }} min-w-full rounded-md border border-krem-300 bg-kartu p-1.5 text-[13px] text-teks-900 hover:border-teks-400 focus:border-biru-700">{{ $baris[$kunci] ?? '' }}</textarea>
                                        </td>
                                    @endforeach
                                    <td class="px-3 py-2">
                                        <input type="text" wire:model="item.{{ $i }}.penanggung_jawab" value="{{ $baris['penanggung_jawab'] ?? '' }}"
                                               class="w-36 min-w-full rounded-md border border-krem-300 bg-kartu p-1.5 text-[13px] text-teks-900 hover:border-teks-400 focus:border-biru-700">
                                    </td>
                                    <td class="px-3 py-2">
                                        <textarea rows="3" wire:model="item.{{ $i }}.indikator_keberhasilan"
                                                  class="w-48 min-w-full rounded-md border border-krem-300 bg-kartu p-1.5 text-[13px] text-teks-900 hover:border-teks-400 focus:border-biru-700">{{ $baris['indikator_keberhasilan'] ?? '' }}</textarea>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" wire:model="item.{{ $i }}.perkiraan_waktu" value="{{ $baris['perkiraan_waktu'] ?? '' }}"
                                               class="w-28 min-w-full rounded-md border border-krem-300 bg-kartu p-1.5 text-[13px] text-teks-900 hover:border-teks-400 focus:border-biru-700">
                                    </td>
                                    <td class="px-3 py-2">
                                        <x-tombol jenis="merusak" ukuran="kecil" wire:click="hapusBaris({{ $i }})">Hapus</x-tombol>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-kartu>
            <p class="-mt-3 text-[12px] text-teks-500">
                {{ count($item) }} butir. Perubahan baru tersimpan setelah Anda menekan “Simpan”.
            </p>
        @endif
    @endif
</div>

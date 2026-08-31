<div class="flex flex-col gap-6">
    <div>
        <h1 class="text-2xl font-bold text-teks-900">Rencana Kerja Tahunan (RKT)</h1>
        <p class="mt-1 max-w-3xl text-teks-700">
            Draf RKT berbasis data, disusun dari hasil analisis akar masalah sekolah. Setiap butir
            dapat Anda sunting, tambah, atau hapus sebelum diunduh dan dibawa ke rapat penyusunan program.
        </p>
    </div>

    @if ($this->sekolah === null)
        <div class="rounded-md border border-krem-300 bg-kartu p-10 text-center">
            <p class="text-teks-700">Belum ada berkas Rapor Pendidikan yang diunggah.</p>
            <a href="{{ route('sekolah.unggah') }}" class="mt-2 inline-block text-[13px] font-semibold text-biru-700 underline">Unggah berkas sekolah</a>
        </div>
    @elseif ($this->analisis === null)
        <div class="rounded-md border border-krem-300 bg-kartu p-10 text-center">
            <p class="text-teks-700">Belum ada analisis untuk sekolah ini.</p>
            <p class="mt-1 text-[13px] text-teks-500">
                Buka <a href="{{ route('sekolah.prioritas') }}" class="text-biru-700 underline">Prioritas &amp; akar masalah</a>
                lalu jalankan analisis lebih dulu.
            </p>
        </div>
    @else
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" wire:click="susunDraf"
                    class="h-9 rounded bg-biru-700 px-4 text-[13px] font-semibold text-white hover:bg-biru-600">
                {{ $rencanaId ? 'Susun ulang draf' : 'Susun draf' }}
            </button>
            @if ($rencanaId)
                <button type="button" wire:click="tambahBaris"
                        class="h-9 rounded border border-biru-700 bg-kartu px-4 text-[13px] font-semibold text-biru-700">
                    Tambah baris
                </button>
                <button type="button" wire:click="simpan"
                        class="h-9 rounded bg-biru-700 px-4 text-[13px] font-semibold text-white hover:bg-biru-600">
                    Simpan
                </button>
            @endif
            @if ($tersimpan)
                <span class="text-[13px] font-medium text-baik">Tersimpan.</span>
            @endif

            <span class="mx-1 h-6 w-px bg-krem-300"></span>
            <button type="button" wire:click="unduhPdf"
                    class="h-9 rounded border border-biru-700 bg-kartu px-4 text-[13px] font-semibold text-biru-700 hover:bg-biru-100">
                Unduh PDF
            </button>
            <button type="button" wire:click="unduhExcel"
                    class="h-9 rounded border border-biru-700 bg-kartu px-4 text-[13px] font-semibold text-biru-700 hover:bg-biru-100">
                Unduh Excel
            </button>
        </div>

        @if ($rencanaId === null)
            <div class="rounded-md border border-krem-300 bg-kartu p-10 text-center">
                <p class="text-teks-700">Belum ada draf RKT untuk analisis ini.</p>
                <p class="mt-1 text-[13px] text-teks-500">Tekan "Susun draf" untuk menghasilkannya dari hasil analisis akar masalah.</p>
            </div>
        @elseif (empty($item))
            <div class="rounded-md border border-krem-300 bg-kartu p-10 text-center">
                <p class="text-teks-700">Draf kosong.</p>
                <p class="mt-1 text-[13px] text-teks-500">
                    Analisis ini belum menghasilkan akar masalah yang cukup didukung bukti.
                    Anda dapat menambah butir RKT secara manual.
                </p>
            </div>
        @else
            <div class="overflow-x-auto rounded-md border border-krem-300 bg-kartu">
                <table class="w-full min-w-[64rem] text-[13px]">
                    <thead>
                        <tr class="border-b border-krem-300 bg-krem-200 text-left text-xs font-semibold uppercase text-teks-700">
                            <th scope="col" class="px-3 py-2">Masalah</th>
                            <th scope="col" class="px-3 py-2">Akar masalah</th>
                            <th scope="col" class="px-3 py-2">Kegiatan</th>
                            <th scope="col" class="px-3 py-2">Penanggung jawab</th>
                            <th scope="col" class="px-3 py-2">Indikator keberhasilan</th>
                            <th scope="col" class="px-3 py-2">Perkiraan waktu</th>
                            <th scope="col" class="px-3 py-2"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item as $i => $baris)
                            <tr wire:key="baris-{{ $i }}" class="border-b border-krem-300 align-top last:border-0">
                                <td class="px-3 py-2">
                                    <textarea rows="3" wire:model="item.{{ $i }}.masalah"
                                              class="w-full min-w-40 rounded border border-krem-300 bg-white p-1.5 text-[13px]">{{ $baris['masalah'] ?? '' }}</textarea>
                                </td>
                                <td class="px-3 py-2">
                                    <textarea rows="3" wire:model="item.{{ $i }}.akar_masalah"
                                              class="w-full min-w-40 rounded border border-krem-300 bg-white p-1.5 text-[13px]">{{ $baris['akar_masalah'] ?? '' }}</textarea>
                                </td>
                                <td class="px-3 py-2">
                                    <textarea rows="3" wire:model="item.{{ $i }}.kegiatan"
                                              class="w-full min-w-56 rounded border border-krem-300 bg-white p-1.5 text-[13px]">{{ $baris['kegiatan'] ?? '' }}</textarea>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" wire:model="item.{{ $i }}.penanggung_jawab" value="{{ $baris['penanggung_jawab'] ?? '' }}"
                                           class="w-full min-w-36 rounded border border-krem-300 bg-white p-1.5 text-[13px]">
                                </td>
                                <td class="px-3 py-2">
                                    <textarea rows="3" wire:model="item.{{ $i }}.indikator_keberhasilan"
                                              class="w-full min-w-48 rounded border border-krem-300 bg-white p-1.5 text-[13px]">{{ $baris['indikator_keberhasilan'] ?? '' }}</textarea>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" wire:model="item.{{ $i }}.perkiraan_waktu" value="{{ $baris['perkiraan_waktu'] ?? '' }}"
                                           class="w-full min-w-28 rounded border border-krem-300 bg-white p-1.5 text-[13px]">
                                </td>
                                <td class="px-3 py-2">
                                    <button type="button" wire:click="hapusBaris({{ $i }})"
                                            class="h-8 rounded border border-kurang px-2 text-xs font-semibold text-kurang">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-[13px] text-teks-500">
                {{ count($item) }} butir. Perubahan baru tersimpan setelah Anda menekan "Simpan".
            </p>
        @endif
    @endif
</div>

<div class="flex flex-col gap-6" @if ($this->adaYangDiproses) wire:poll.5s @endif>
    <x-kepala-halaman
        judul="Impor berkas"
        lead="Riwayat dan status setiap berkas Rapor Pendidikan yang dimuat ke basis data." />

    <x-kartu judul="Berkas Rapor Pendidikan Indonesia (level daerah)">
        <p class="text-[13px] text-teks-700">
            Berukuran 16&ndash;21 MB dengan 38 sheet provinsi, sehingga diproses di komputer lokal,
            bukan diunggah lewat halaman ini. Jalankan:
        </p>
        <code class="mt-2 block overflow-x-auto rounded-md border border-krem-300 bg-krem-100 px-3 py-2 text-[12px] text-teks-900">
            php artisan akar:impor storage/app/rapor/berkas.xlsx --async
        </code>
        <p class="mt-2 text-[13px] text-teks-700">
            Setiap sheet provinsi menjadi satu pekerjaan antrean terpisah. Bila satu sheet gagal,
            sisanya tetap diproses dan sheet yang gagal dapat diulang.
        </p>
    </x-kartu>

    @if ($this->riwayat->isEmpty())
        <x-kartu rapat>
            <x-kosong ikon="unggah" judul="Belum ada berkas yang diimpor"
                      pesan="Mulai dengan menjalankan perintah impor di atas, atau unggah berkas satuan pendidikan lewat menu Mode satuan pendidikan." />
        </x-kartu>
    @else
        <x-kartu rapat>
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-krem-300 bg-krem-200 text-left">
                            @foreach (['Berkas', 'Jenis', 'Edisi', 'Status'] as $kol)
                                <th scope="col" class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">{{ $kol }}</th>
                            @endforeach
                            <th scope="col" class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Baris data</th>
                            <th scope="col" class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-500">Diproses</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->riwayat as $berkas)
                            @php
                                $gaya = match ($berkas->status) {
                                    'selesai' => ['text-baik border-baik bg-baik-bg', 'Selesai'],
                                    'proses' => ['text-sedang border-sedang bg-sedang-bg', 'Diproses'],
                                    'gagal' => ['text-kurang border-kurang bg-kurang-bg', 'Gagal'],
                                    default => ['text-kosong border-kosong bg-kosong-bg', 'Menunggu diproses'],
                                };
                            @endphp
                            <tr class="border-b border-krem-300 align-top last:border-0">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-teks-900">{{ $berkas->nama_berkas }}</span>
                                    @if ($berkas->catatan_galat)
                                        <p class="mt-1 whitespace-pre-line text-[11px] leading-relaxed text-kurang">{{ $berkas->catatan_galat }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 capitalize text-teks-700">{{ $berkas->jenis }}</td>
                                <td class="tabular px-4 py-3 text-teks-700">{{ $berkas->tahun_edisi ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded border px-2 py-0.5 text-[11px] font-semibold leading-none {{ $gaya[0] }}">{{ $gaya[1] }}</span>
                                </td>
                                <td class="tabular px-4 py-3 text-right text-teks-900">
                                    {{ $berkas->jumlah_baris ? number_format($berkas->jumlah_baris, 0, ',', '.') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-[12px] text-teks-500">
                                    {{ $berkas->diproses_pada?->translatedFormat('d M Y H:i') ?? $berkas->created_at?->translatedFormat('d M Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-kartu>
    @endif
</div>

<div class="flex flex-col gap-6" @if ($this->adaYangDiproses) wire:poll.5s @endif>
    <div>
        <h1 class="text-2xl font-bold text-teks-900">Impor berkas</h1>
        <p class="mt-1 max-w-3xl text-teks-700">
            Riwayat dan status setiap berkas Rapor Pendidikan yang dimuat ke basis data.
        </p>
    </div>

    <div class="rounded-md border border-krem-300 bg-biru-100 p-4 text-[13px] text-teks-700">
        <p class="font-semibold text-teks-900">Berkas Rapor Pendidikan Indonesia (level daerah)</p>
        <p class="mt-1">
            Berukuran 16&ndash;21 MB dengan 38 sheet provinsi, sehingga diproses di komputer lokal,
            bukan diunggah lewat halaman ini. Jalankan:
        </p>
        <code class="mt-2 block rounded border border-krem-300 bg-kartu px-3 py-2 text-teks-900">
            php artisan akar:impor storage/app/rapor/berkas.xlsx --async
        </code>
        <p class="mt-2">
            Setiap sheet provinsi menjadi satu pekerjaan antrean terpisah. Bila satu sheet gagal,
            sisanya tetap diproses dan sheet yang gagal dapat diulang.
        </p>
    </div>

    @if ($this->riwayat->isEmpty())
        <div class="rounded-md border border-krem-300 bg-kartu p-10 text-center">
            <p class="text-teks-700">Belum ada berkas yang diimpor.</p>
            <p class="mt-1 text-[13px] text-teks-500">
                Mulai dengan menjalankan perintah impor di atas, atau unggah berkas satuan pendidikan
                lewat menu Mode sekolah.
            </p>
        </div>
    @else
        <div class="overflow-hidden rounded-md border border-krem-300 bg-kartu">
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-krem-300 bg-krem-200 text-left text-xs font-semibold uppercase text-teks-700">
                            <th scope="col" class="px-5 py-2">Berkas</th>
                            <th scope="col" class="px-5 py-2">Jenis</th>
                            <th scope="col" class="px-5 py-2">Edisi</th>
                            <th scope="col" class="px-5 py-2">Status</th>
                            <th scope="col" class="px-5 py-2 text-right">Baris data</th>
                            <th scope="col" class="px-5 py-2">Diproses</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->riwayat as $berkas)
                            @php
                                $gaya = match ($berkas->status) {
                                    'selesai' => ['text-baik', 'bg-baik-bg', 'border-baik', 'Selesai'],
                                    'proses' => ['text-sedang', 'bg-sedang-bg', 'border-sedang', 'Diproses'],
                                    'gagal' => ['text-kurang', 'bg-kurang-bg', 'border-kurang', 'Gagal'],
                                    default => ['text-kosong', 'bg-kosong-bg', 'border-kosong', 'Menunggu diproses'],
                                };
                            @endphp
                            <tr class="border-b border-krem-300 align-top last:border-0">
                                <td class="px-5 py-3">
                                    <span class="font-medium text-teks-900">{{ $berkas->nama_berkas }}</span>
                                    @if ($berkas->catatan_galat)
                                        <p class="mt-1 whitespace-pre-line text-xs text-kurang">{{ $berkas->catatan_galat }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3 capitalize text-teks-700">{{ $berkas->jenis }}</td>
                                <td class="px-5 py-3 text-teks-700">{{ $berkas->tahun_edisi ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center rounded border px-2.5 py-1 text-xs font-semibold {{ $gaya[0] }} {{ $gaya[1] }} {{ $gaya[2] }}">
                                        {{ $gaya[3] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right tabular text-teks-900">
                                    {{ $berkas->jumlah_baris ? number_format($berkas->jumlah_baris, 0, ',', '.') : '—' }}
                                </td>
                                <td class="px-5 py-3 text-teks-500">
                                    {{ $berkas->diproses_pada?->translatedFormat('d M Y H:i') ?? $berkas->created_at?->translatedFormat('d M Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

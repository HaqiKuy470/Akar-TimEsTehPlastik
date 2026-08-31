@props(['nomor' => '', 'nama' => '', 'akar' => null])

{{--
    Pohon akar masalah — daftar bertingkat dengan garis penghubung, bukan
    diagram interaktif (DESIGN.md 5). Baris yang menjadi bukti diberi latar
    biru-100 dan penanda "← bukti".

    Dipanggil dari halaman Prioritas (dinas & sekolah):
        <x-pohon-akar :nomor="$item['nomor']" :nama="$item['nama']" :akar="$item['akar']" />

    Bentuk $akar: ['dipetakan' => bool, 'induk_label' => string,
                   'kandidat' => [ ['label','keyakinan','keyakinan_kode','bukti'=>[['nomor','nama','label']]] ]]
--}}

@php
    $chip = [
        'kuat' => 'border-biru-700 bg-biru-100 text-navy-900',
        'sedang' => 'border-sedang bg-sedang-bg text-sedang',
        'lemah' => 'border-kosong text-kosong',
        'tidak_cukup_bukti' => 'border-teks-300 text-teks-400',
    ];
@endphp

<div class="rounded-md border border-krem-300 bg-krem-100 p-4">
    @if (! $akar || empty($akar['dipetakan']))
        <p class="text-[13px] text-teks-500">
            Rekomendasi akar masalah belum tersedia untuk indikator ini.
            Indikator ini belum termasuk 15–20 indikator prioritas yang dipetakan pada tahap ini.
        </p>
    @else
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[13px] font-semibold text-teks-900">{{ $nomor }} {{ $nama }}</span>
            <x-badge-capaian :label="$akar['induk_label']" />
        </div>

        <div class="mt-1 space-y-3 border-l border-krem-300 pl-4">
            @foreach ($akar['kandidat'] as $i => $kandidat)
                @php
                    $kode = $kandidat['keyakinan_kode'] ?? 'tidak_cukup_bukti';
                    $terkuat = $i === 0 && $kode !== 'tidak_cukup_bukti';
                @endphp
                <div @class(['pt-3', 'border-l-2 border-biru-700 -ml-4 pl-4' => $terkuat])>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($terkuat)
                            <span class="text-[10px] font-semibold uppercase tracking-[0.06em] text-emas-700">Akar masalah terkuat</span>
                        @endif
                        <span class="text-[13px] font-semibold text-teks-900">{{ $kandidat['label'] }}</span>
                        <span class="rounded border px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-[0.04em] {{ $chip[$kode] ?? $chip['tidak_cukup_bukti'] }}">
                            {{ $kandidat['keyakinan'] }}
                        </span>
                    </div>

                    @if (! empty($kandidat['bukti']))
                        <ul class="mt-2 flex flex-col gap-1">
                            @foreach ($kandidat['bukti'] as $bukti)
                                <li class="flex flex-wrap items-center gap-2 rounded bg-biru-100 px-2 py-1 text-[12px]">
                                    <span class="font-medium text-teks-900">{{ $bukti['nomor'] }}</span>
                                    <span class="text-teks-700">{{ $bukti['nama'] }}</span>
                                    <x-badge-capaian :label="$bukti['label']" />
                                    <span class="text-[10px] font-medium text-biru-700">&larr; bukti</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-1 text-[12px] text-teks-500">
                            Belum ada indikator pendukung yang berlabel Kurang atau Sedang.
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

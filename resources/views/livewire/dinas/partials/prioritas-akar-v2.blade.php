<div class="prioritas-root-panel">
    <div class="prioritas-root-panel__head">
        <div>
            <span class="prioritas-kicker">Akar masalah</span>
            <h4>{{ $item['nomor'] }} {{ $item['nama'] }}</h4>
        </div>
        <x-badge-capaian :label="$item['akar']['induk_label']" />
    </div>

    @if (! $item['akar']['dipetakan'])
        <p class="prioritas-root-panel__empty">
            Rekomendasi akar masalah belum tersedia untuk indikator ini.
            Indikator ini belum termasuk indikator prioritas yang dipetakan pada tahap ini.
        </p>
    @else
        <div class="prioritas-root-candidates">
            @foreach ($item['akar']['kandidat'] as $kIndex => $kandidat)
                <article class="prioritas-root-candidate">
                    <div class="prioritas-root-candidate__top">
                        <span class="prioritas-root-candidate__index">
                            {{ str_pad((string) ($kIndex + 1), 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <div>
                            <div class="prioritas-root-candidate__title">
                                <h5>{{ $kandidat['label'] }}</h5>

                                <span @class([
                                    'prioritas-confidence',
                                    'prioritas-confidence--strong' => $kandidat['keyakinan_kode'] === 'kuat',
                                    'prioritas-confidence--medium' => $kandidat['keyakinan_kode'] === 'sedang',
                                    'prioritas-confidence--weak' => in_array($kandidat['keyakinan_kode'], ['lemah', 'tidak_cukup_bukti']),
                                ])>
                                    {{ $kandidat['keyakinan'] }}
                                </span>

                                @if ($kIndex === 0 && $kandidat['keyakinan_kode'] !== 'tidak_cukup_bukti')
                                    <span class="prioritas-root-candidate__best">Akar terkuat</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if (count($kandidat['bukti']) > 0)
                        <div class="prioritas-evidence-list">
                            @foreach ($kandidat['bukti'] as $bukti)
                                <div class="prioritas-evidence">
                                    <span class="prioritas-evidence__numb`er">{{ $bukti['nomor'] }}</span>
                                    <strong>{{ $bukti['nama'] }}</strong>
                                    <x-badge-capaian :label="$bukti['label']" />
                                    <span class="prioritas-evidence__tag">bukti →</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="prioritas-root-candidate__empty">
                            Belum ada indikator pendukung yang berlabel Kurang atau Sedang.
                        </p>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</div>

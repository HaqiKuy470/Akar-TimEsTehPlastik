<div class="prioritas-v2">
    @php
        $jumlahPrioritas = count($daftar);
        $utama = $jumlahPrioritas > 0 ? $daftar[0] : null;
        $namaWilayah = null;

        if ($wilayahId) {
            $wilayahTerpilih = $this->kabkotaTersedia->firstWhere('id', (int) $wilayahId);
            $namaWilayah = $wilayahTerpilih?->kabupaten_kota;
        }
    @endphp

    <section class="prioritas-hero">
        <div class="prioritas-hero__grid">
            <div>
                <div class="prioritas-eyebrow">
                    <span>AKAR / ANALISIS DAERAH</span>
                    <span class="prioritas-eyebrow__line"></span>
                </div>

                <h1 class="prioritas-display-title">
                    Masalah yang
                    <span>perlu didahulukan.</span>
                </h1>

                <p class="prioritas-hero__lead">
                    AKAR mengurutkan indikator bermasalah berdasarkan skor prioritas,
                    lalu membantu menelusuri faktor yang paling mungkin menjadi akar masalahnya.
                </p>
            </div>

            <aside class="prioritas-hero__context">
                <span class="prioritas-kicker">Konteks analisis</span>

                @if ($namaWilayah && $jenisSatuan && $statusSatuan)
                    <h2 class="prioritas-hero__location">{{ $namaWilayah }}</h2>
                    <p class="prioritas-hero__meta">
                        {{ $provinsi }} · {{ $jenisSatuan }} · {{ $statusSatuan }} · {{ $tahun }}
                    </p>

                    <div class="prioritas-hero__metric">
                        <div>
                            <span class="prioritas-hero__metric-value">
                                {{ $sudahDijalankan ? $jumlahPrioritas : '—' }}
                            </span>
                            <span class="prioritas-hero__metric-label">indikator prioritas</span>
                        </div>

                        <div class="prioritas-hero__metric-side">
                            <span class="prioritas-hero__metric-value">
                                @if ($sudahDijalankan && $utama)
                                    #{{ $utama['peringkat_prioritas'] }}
                                @else
                                    —
                                @endif
                            </span>
                            <span class="prioritas-hero__metric-label">prioritas utama</span>
                        </div>
                    </div>

                    @if ($sudahDijalankan && $utama)
                        <p class="prioritas-hero__footnote">
                            {{ $utama['nomor'] }} · {{ $utama['nama'] }}
                        </p>
                    @endif
                @else
                    <div class="prioritas-hero__placeholder" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                @endif
            </aside>
        </div>
    </section>

    <section class="prioritas-section prioritas-section--filter">
        <div class="prioritas-section__heading">
            <span class="prioritas-section__index">01</span>

            <div>
                <span class="prioritas-kicker">Parameter analisis</span>
                <h2 class="prioritas-section__title">Pilih data yang ingin dianalisis.</h2>
            </div>

            <p class="prioritas-section__aside">
                Gunakan kombinasi yang sama seperti pada Profil Capaian agar hasil
                prioritas dapat dibaca dalam konteks wilayah yang tepat.
            </p>
        </div>

        <div class="prioritas-filter-panel">
            <label class="prioritas-field">
                <span class="prioritas-field__label">Tahun data</span>
                <span class="prioritas-select-wrap">
                    <select class="prioritas-select" wire:model.live="tahun">
                        @foreach ($this->tahunTersedia as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </span>
            </label>

            <label class="prioritas-field">
                <span class="prioritas-field__label">Provinsi</span>
                <span class="prioritas-select-wrap">
                    <select class="prioritas-select" wire:model.live="provinsi">
                        <option value="">— pilih —</option>
                        @foreach ($this->provinsiTersedia as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </select>
                </span>
            </label>

            <label class="prioritas-field">
                <span class="prioritas-field__label">Kabupaten / Kota</span>
                <span class="prioritas-select-wrap">
                    <select class="prioritas-select" wire:model.live="wilayahId" @disabled($provinsi === '')>
                        <option value="">— pilih —</option>
                        @foreach ($this->kabkotaTersedia as $w)
                            <option value="{{ $w->id }}">{{ $w->kabupaten_kota }}</option>
                        @endforeach
                    </select>
                </span>
            </label>

            <label class="prioritas-field">
                <span class="prioritas-field__label">Jenjang</span>
                <span class="prioritas-select-wrap">
                    <select class="prioritas-select" wire:model.live="jenisSatuan">
                        <option value="">— pilih —</option>
                        @foreach ($this->jenisSatuanTersedia as $j)
                            <option value="{{ $j }}">{{ $j }}</option>
                        @endforeach
                    </select>
                </span>
            </label>

            <label class="prioritas-field">
                <span class="prioritas-field__label">Status satuan</span>
                <span class="prioritas-select-wrap">
                    <select class="prioritas-select" wire:model.live="statusSatuan" @disabled($jenisSatuan === '')>
                        <option value="">— pilih —</option>
                        @foreach ($this->statusSatuanTersedia as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </span>
            </label>
        </div>

        <div class="prioritas-actionbar">
            <div class="prioritas-actionbar__copy">
                <span class="prioritas-kicker">Mesin analisis</span>

                @if (! $this->siapDijalankan)
                    <p>Lengkapi seluruh pilihan untuk mulai menghitung prioritas.</p>
                @elseif (! $sudahDijalankan)
                    <p>Semua parameter siap. Jalankan analisis untuk menghasilkan peringkat.</p>
                @else
                    <p>Analisis aktif. Jalankan ulang kapan pun bila parameter atau data berubah.</p>
                @endif
            </div>

            <div class="prioritas-actionbar__actions">
                @if ($sudahDijalankan && $jumlahPrioritas > 0)
                    <div class="prioritas-download">
                        <span>Unduh</span>
                        <button type="button" class="prioritas-link-button" wire:click="unduhPdf">PDF ↗</button>
                        <button type="button" class="prioritas-link-button" wire:click="unduhExcel">Excel ↗</button>
                    </div>
                @endif

                <button
                    type="button"
                    class="prioritas-run-button"
                    wire:click="jalankan"
                    wire:loading.attr="disabled"
                    @disabled(! $this->siapDijalankan)
                >
                    <span wire:loading.remove wire:target="jalankan">
                        {{ $sudahDijalankan ? 'Jalankan ulang' : 'Jalankan analisis' }}
                    </span>
                    <span wire:loading wire:target="jalankan">Menghitung…</span>
                    <span aria-hidden="true">→</span>
                </button>
            </div>
        </div>
    </section>

    <div wire:loading.delay.flex wire:target="jalankan" class="prioritas-loading">
        <div class="rangka-muat prioritas-loading__top"></div>
        <div class="rangka-muat prioritas-loading__row"></div>
        <div class="rangka-muat prioritas-loading__row"></div>
        <div class="rangka-muat prioritas-loading__row"></div>
    </div>

    <div wire:loading.remove wire:target="jalankan">
        @if (! $this->siapDijalankan)
            <section class="prioritas-empty">
                <span class="prioritas-empty__index">02</span>
                <div class="prioritas-empty__mark">↘</div>
                <div class="prioritas-empty__body">
                    <span class="prioritas-kicker">Belum ada konteks</span>
                    <h2>Pilih wilayah lebih dulu.</h2>
                    <p>
                        Setelah semua parameter dipilih, AKAR bisa membaca indikator bermasalah
                        dan menghitung mana yang paling mendesak.
                    </p>
                </div>
            </section>
        @elseif (! $sudahDijalankan)
            <section class="prioritas-empty">
                <span class="prioritas-empty__index">02</span>
                <div class="prioritas-empty__mark">01</div>
                <div class="prioritas-empty__body">
                    <span class="prioritas-kicker">Siap dianalisis</span>
                    <h2>Data sudah lengkap. Sekarang jalankan analisis.</h2>
                    <p>
                        Sistem akan menghitung skor prioritas dari indikator yang berlabel
                        Kurang atau Sedang dan menyusunnya dari yang paling mendesak.
                    </p>

                    <button
                        type="button"
                        class="prioritas-inline-cta"
                        wire:click="jalankan"
                        @disabled(! $this->siapDijalankan)
                    >
                        Jalankan analisis <span>→</span>
                    </button>
                </div>
            </section>
        @elseif ($jumlahPrioritas === 0)
            <section class="prioritas-empty prioritas-empty--good">
                <span class="prioritas-empty__index">02</span>
                <div class="prioritas-empty__mark">✓</div>
                <div class="prioritas-empty__body">
                    <span class="prioritas-kicker">Tidak ada masalah mendesak</span>
                    <h2>Tidak ditemukan indikator bermasalah.</h2>
                    <p>
                        Tidak ada indikator berlabel Kurang atau Sedang untuk kombinasi ini.
                        Pertahankan praktik yang sudah berjalan.
                    </p>
                </div>
            </section>
        @else
            <section class="prioritas-section prioritas-section--ranking">
                <div class="prioritas-section__heading">
                    <span class="prioritas-section__index">02</span>

                    <div>
                        <span class="prioritas-kicker">Hasil analisis</span>
                        <h2 class="prioritas-section__title">Urutan masalah yang perlu didahulukan.</h2>
                    </div>

                    <p class="prioritas-section__aside">
                        {{ $jumlahPrioritas }} indikator masuk daftar prioritas.
                        Buka rincian skor untuk memahami alasan urutannya, atau telusuri akar masalahnya.
                    </p>
                </div>

                @if ($utama)
                    <article class="prioritas-featured akar-reveal">
                        <div class="prioritas-featured__rank">
                            <span>PRIORITAS</span>
                            <strong>#{{ $utama['peringkat_prioritas'] }}</strong>
                        </div>

                        <div class="prioritas-featured__main">
                            <span class="prioritas-featured__number">{{ $utama['nomor'] }}</span>
                            <h3>{{ $utama['nama'] }}</h3>
                            <p>{{ $utama['kalimat_penjelas'] }}</p>

                            <div class="prioritas-featured__meta">
                                <x-badge-capaian :label="$utama['label']" />
                                <x-arah-perubahan :nilai="$utama['perubahan']" />

                                @if ($utama['peringkat_teks'])
                                    <span>{{ $utama['peringkat_teks'] }} kabupaten/kota</span>
                                @endif
                            </div>
                        </div>

                        <div class="prioritas-featured__score">
                            <span>Skor prioritas</span>
                            <strong>{{ rtrim(rtrim(number_format((float) $utama['skor'], 1, ',', '.'), '0'), ',') }}</strong>
                        </div>

                        <div class="prioritas-featured__actions">
                            <button type="button" wire:click="toggleRincian({{ $utama['id'] }})">
                                {{ $utama['rincian_terbuka'] ? 'Tutup rincian' : 'Rincian skor' }}
                                <span>{{ $utama['rincian_terbuka'] ? '↑' : '↓' }}</span>
                            </button>
                            <button type="button" wire:click="toggleAkar({{ $utama['id'] }})">
                                {{ $utama['akar_terbuka'] ? 'Tutup akar masalah' : 'Telusuri akar masalah' }}
                                <span>{{ $utama['akar_terbuka'] ? '↑' : '→' }}</span>
                            </button>
                        </div>

                        @if ($utama['rincian_terbuka'] || ($utama['akar_terbuka'] && $utama['akar']))
                            <div class="prioritas-featured__expand">
                                @if ($utama['rincian_terbuka'])
                                    <div class="prioritas-expand-panel">
                                        <div class="prioritas-expand-panel__head">
                                            <span class="prioritas-kicker">Rincian skor</span>
                                            <p>Setiap komponen dapat ditelusuri kembali ke data sumber.</p>
                                        </div>
                                        <x-rincian-skor :komponen="$utama['komponen_skor']" :skor="$utama['skor']" />
                                    </div>
                                @endif

                                @if ($utama['akar_terbuka'] && $utama['akar'])
                                    @include('livewire.dinas.partials.prioritas-akar-v2', ['item' => $utama])
                                @endif
                            </div>
                        @endif
                    </article>
                @endif

                @if ($jumlahPrioritas > 1)
                    <div class="prioritas-list">
                        @foreach ($daftar as $item)
                            @continue($item['peringkat_prioritas'] == 1)

                            <article
                                wire:key="prioritas-{{ $item['id'] }}"
                                class="prioritas-row akar-reveal"
                                style="--akar-delay: {{ min(($loop->index + 1) * 55, 330) }}ms"
                            >
                                <div class="prioritas-row__rank">
                                    <span>#{{ $item['peringkat_prioritas'] }}</span>
                                </div>

                                <div class="prioritas-row__main">
                                    <span class="prioritas-row__number">{{ $item['nomor'] }}</span>
                                    <h3>{{ $item['nama'] }}</h3>
                                    <p>{{ $item['kalimat_penjelas'] }}</p>

                                    <div class="prioritas-row__meta">
                                        <x-badge-capaian :label="$item['label']" />
                                        <x-arah-perubahan :nilai="$item['perubahan']" />

                                        @if ($item['peringkat_teks'])
                                            <span>{{ $item['peringkat_teks'] }} kabupaten/kota</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="prioritas-row__score">
                                    <strong>{{ rtrim(rtrim(number_format((float) $item['skor'], 1, ',', '.'), '0'), ',') }}</strong>
                                    <span>skor</span>
                                </div>

                                <div class="prioritas-row__actions">
                                    <button type="button" wire:click="toggleRincian({{ $item['id'] }})">
                                        Rincian
                                        <span>{{ $item['rincian_terbuka'] ? '↑' : '↓' }}</span>
                                    </button>
                                    <button type="button" wire:click="toggleAkar({{ $item['id'] }})">
                                        Akar
                                        <span>{{ $item['akar_terbuka'] ? '↑' : '→' }}</span>
                                    </button>
                                </div>

                                @if ($item['rincian_terbuka'] || ($item['akar_terbuka'] && $item['akar']))
                                    <div class="prioritas-row__expand">
                                        @if ($item['rincian_terbuka'])
                                            <div class="prioritas-expand-panel">
                                                <div class="prioritas-expand-panel__head">
                                                    <span class="prioritas-kicker">Rincian skor</span>
                                                    <p>Komponen pembentuk skor prioritas.</p>
                                                </div>
                                                <x-rincian-skor :komponen="$item['komponen_skor']" :skor="$item['skor']" />
                                            </div>
                                        @endif

                                        @if ($item['akar_terbuka'] && $item['akar'])
                                            @include('livewire.dinas.partials.prioritas-akar-v2', ['item' => $item])
                                        @endif
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif
    </div>
</div>

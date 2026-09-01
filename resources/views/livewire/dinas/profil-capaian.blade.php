@php
    $profil = $this->profil;

    $ringkasan = $profil['ringkasan'] ?? [
        'merah' => 0,
        'kuning' => 0,
        'hijau' => 0,
        'tidak_tersedia' => 0,
        'total' => 0,
    ];

    $total = (int) ($ringkasan['total'] ?? 0);
    $tersedia = max(0, $total - (int) ($ringkasan['tidak_tersedia'] ?? 0));
    $persenBaik = $tersedia > 0
        ? (int) round(((int) $ringkasan['hijau'] / $tersedia) * 100)
        : 0;

    $namaWilayah = $profil['wilayah']['nama'] ?? 'Pilih wilayah';
    $konteks = $profil
        ? collect([
            $profil['jenis_satuan'] ?? null,
            $profil['status_satuan'] ?? null,
            $profil['tahun'] ?? null,
        ])->filter()->implode(' · ')
        : 'Lengkapi filter untuk membaca profil capaian.';
@endphp

<div class="profil-capaian-v2">
    <section class="akar-hero" aria-labelledby="judul-profil-capaian">
        <div class="akar-hero__grid">
            <div class="akar-hero__copy">
                <div class="akar-eyebrow">
                    <span>AKAR</span>
                    <span class="akar-eyebrow__line" aria-hidden="true"></span>
                    <span>Profil daerah</span>
                </div>

                <h1 id="judul-profil-capaian" class="akar-display-title">
                    Profil capaian
                    <span>daerah.</span>
                </h1>

                <p class="akar-hero__lead">
                    Baca kondisi mutu pendidikan satu wilayah secara utuh—dari gambaran besar sampai indikator yang perlu ditindaklanjuti.
                </p>

                <div class="mt-4">
                    <x-tautan-panduan anchor="profil" />
                </div>
            </div>

            <div class="akar-hero__context" aria-live="polite">
                <p class="akar-kicker">Konteks aktif</p>
                <p class="akar-hero__location">{{ $namaWilayah }}</p>
                <p class="akar-hero__meta">{{ $konteks }}</p>

                @if ($profil && $profil['tersedia'])
                    <div class="akar-hero__metric">
                        <div>
                            <span class="akar-hero__metric-value tabular">{{ $total }}</span>
                            <span class="akar-hero__metric-label">indikator</span>
                        </div>
                        <div class="akar-hero__metric-side">
                            <span class="akar-hero__metric-value tabular">{{ $persenBaik }}%</span>
                            <span class="akar-hero__metric-label">berlabel baik*</span>
                        </div>
                    </div>
                    <p class="akar-hero__footnote">*Dihitung dari indikator yang memiliki data.</p>
                @else
                    <div class="akar-hero__placeholder" aria-hidden="true">
                        <span></span><span></span><span></span>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="akar-section akar-section--filter" aria-labelledby="judul-filter-capaian">
        <div class="akar-section__heading">
            <span class="akar-section__index">01</span>
            <div>
                <p class="akar-kicker">Atur konteks</p>
                <h2 id="judul-filter-capaian" class="akar-section__title">Pilih data yang ingin dibaca.</h2>
            </div>
            <p class="akar-section__aside">Filter diperbarui langsung saat pilihan berubah.</p>
        </div>

        <div class="akar-filter-panel">
            <label class="akar-field">
                <span class="akar-field__label">Tahun data</span>
                <span class="akar-select-wrap">
                    <select class="akar-select" wire:model.live="tahun">
                        @foreach ($this->tahunTersedia as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </span>
            </label>

            <label class="akar-field">
                <span class="akar-field__label">Provinsi</span>
                <span class="akar-select-wrap">
                    <select class="akar-select" wire:model.live="provinsi">
                        <option value="">— pilih —</option>
                        @foreach ($this->provinsiTersedia as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </select>
                </span>
            </label>

            <label class="akar-field">
                <span class="akar-field__label">Kabupaten / Kota</span>
                <span class="akar-select-wrap">
                    <select class="akar-select" wire:model.live="wilayahId" @disabled($provinsi === '')>
                        <option value="">— pilih —</option>
                        @foreach ($this->kabkotaTersedia as $w)
                            <option value="{{ $w->id }}">{{ $w->kabupaten_kota }}</option>
                        @endforeach
                    </select>
                </span>
            </label>

            <label class="akar-field">
                <span class="akar-field__label">Jenjang</span>
                <span class="akar-select-wrap">
                    <select class="akar-select" wire:model.live="jenisSatuan">
                        <option value="">— pilih —</option>
                        @foreach ($this->jenisSatuanTersedia as $j)
                            <option value="{{ $j }}">{{ $j }}</option>
                        @endforeach
                    </select>
                </span>
            </label>

            <label class="akar-field">
                <span class="akar-field__label">Status satuan</span>
                <span class="akar-select-wrap">
                    <select class="akar-select" wire:model.live="statusSatuan" @disabled($jenisSatuan === '')>
                        <option value="">— pilih —</option>
                        @foreach ($this->statusSatuanTersedia as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </span>
            </label>
        </div>
    </section>

    <div
        wire:loading.delay.flex
        wire:target="tahun,provinsi,wilayahId,jenisSatuan,statusSatuan"
        class="akar-loading"
        role="status"
        aria-label="Memuat profil capaian"
    >
        <div class="rangka-muat akar-loading__hero"></div>
        <div class="rangka-muat akar-loading__line"></div>
        <div class="rangka-muat akar-loading__block"></div>
    </div>

    <div wire:loading.remove wire:target="tahun,provinsi,wilayahId,jenisSatuan,statusSatuan">
        @if ($profil === null)
            <section class="akar-empty akar-reveal" aria-labelledby="judul-kosong-profil">
                <span class="akar-empty__index">02</span>
                <div class="akar-empty__mark" aria-hidden="true">↘</div>
                <div class="akar-empty__body">
                    <p class="akar-kicker">Belum ada konteks</p>
                    <h2 id="judul-kosong-profil">Mulai dari lima pilihan di atas.</h2>
                    <p>Pilih provinsi, kabupaten/kota, jenjang, dan status satuan pendidikan untuk menampilkan profil capaian.</p>
                </div>
            </section>
        @elseif (! $profil['tersedia'])
            <section class="akar-empty akar-empty--warning akar-reveal" aria-labelledby="judul-data-kosong">
                <span class="akar-empty__index">02</span>
                <div class="akar-empty__mark" aria-hidden="true">!</div>
                <div class="akar-empty__body">
                    <p class="akar-kicker">Data belum tersedia</p>
                    <h2 id="judul-data-kosong">Kombinasi ini belum punya capaian.</h2>
                    <p>
                        Belum ada berkas Rapor Pendidikan tahun {{ $profil['tahun'] }} yang selesai diimpor,
                        atau kombinasi jenjang dan status ini tidak ada di berkas.
                    </p>
                </div>
            </section>
        @else
            <section class="akar-section akar-reveal" aria-labelledby="judul-ringkasan-capaian">
                <div class="akar-section__heading">
                    <span class="akar-section__index">02</span>
                    <div>
                        <p class="akar-kicker">Snapshot</p>
                        <h2 id="judul-ringkasan-capaian" class="akar-section__title">Satu pandangan, empat kondisi.</h2>
                    </div>
                    <p class="akar-section__aside">
                        {{ $profil['wilayah']['nama'] }} · {{ $profil['jenis_satuan'] }} · {{ $profil['status_satuan'] }}
                    </p>
                </div>

                <div class="akar-summary-grid">
                    <article class="akar-stat akar-stat--kurang">
                        <span class="akar-stat__eyebrow">Perlu perhatian</span>
                        <strong class="akar-stat__value tabular">{{ $ringkasan['merah'] }}</strong>
                        <span class="akar-stat__caption">indikator berlabel kurang</span>
                    </article>

                    <article class="akar-stat akar-stat--sedang">
                        <span class="akar-stat__eyebrow">Cukup</span>
                        <strong class="akar-stat__value tabular">{{ $ringkasan['kuning'] }}</strong>
                        <span class="akar-stat__caption">indikator berlabel sedang</span>
                    </article>

                    <article class="akar-stat akar-stat--baik">
                        <span class="akar-stat__eyebrow">Baik</span>
                        <strong class="akar-stat__value tabular">{{ $ringkasan['hijau'] }}</strong>
                        <span class="akar-stat__caption">indikator berlabel baik</span>
                    </article>

                    <article class="akar-stat akar-stat--kosong">
                        <span class="akar-stat__eyebrow">Tidak tersedia</span>
                        <strong class="akar-stat__value tabular">{{ $ringkasan['tidak_tersedia'] }}</strong>
                        <span class="akar-stat__caption">indikator tanpa data</span>
                    </article>
                </div>

                @php
                    $basisDistribusi = max(1, $total);
                    $lebarKurang = ((int) $ringkasan['merah'] / $basisDistribusi) * 100;
                    $lebarSedang = ((int) $ringkasan['kuning'] / $basisDistribusi) * 100;
                    $lebarBaik = ((int) $ringkasan['hijau'] / $basisDistribusi) * 100;
                    $lebarKosong = ((int) $ringkasan['tidak_tersedia'] / $basisDistribusi) * 100;
                @endphp

                <div class="akar-distribution" aria-label="Distribusi capaian indikator">
                    <div class="akar-distribution__track" aria-hidden="true">
                        <span class="akar-distribution__segment akar-distribution__segment--kurang" style="width: {{ $lebarKurang }}%"></span>
                        <span class="akar-distribution__segment akar-distribution__segment--sedang" style="width: {{ $lebarSedang }}%"></span>
                        <span class="akar-distribution__segment akar-distribution__segment--baik" style="width: {{ $lebarBaik }}%"></span>
                        <span class="akar-distribution__segment akar-distribution__segment--kosong" style="width: {{ $lebarKosong }}%"></span>
                    </div>
                    <div class="akar-distribution__meta">
                        <span>{{ $total }} indikator diukur</span>
                        <span>{{ $persenBaik }}% dari data tersedia berlabel baik</span>
                    </div>
                </div>

                @if (! empty($profil['dimensi_grafik']))
                    <div class="akar-chart-wrap">
                        <x-grafik-komposisi :dimensi="$profil['dimensi_grafik']"
                            judul="Sebaran per dimensi" />
                    </div>
                @endif
            </section>

            <section class="akar-section akar-section--indicators" aria-labelledby="judul-indikator-capaian">
                <div class="akar-section__heading akar-section__heading--sticky-intro">
                    <span class="akar-section__index">03</span>
                    <div>
                        <p class="akar-kicker">Detail indikator</p>
                        <h2 id="judul-indikator-capaian" class="akar-section__title">Baca per dimensi.</h2>
                    </div>
                    <p class="akar-section__aside">Arah perubahan membantu melihat apakah kondisi bergerak membaik, memburuk, atau tetap.</p>
                </div>

                <div class="akar-dimensions">
                    @foreach ($profil['dimensi'] as $kode => $dim)
                        <article class="akar-dimension akar-reveal" style="--akar-delay: {{ min($loop->index * 45, 270) }}ms">
                            <header class="akar-dimension__header">
                                <div class="akar-dimension__code">{{ $kode }}</div>
                                <div class="akar-dimension__title-wrap">
                                    <h3>{{ $dim['nama'] }}</h3>
                                    <p>{{ count($dim['indikator']) }} indikator</p>
                                </div>
                                <div class="akar-dimension__line" aria-hidden="true"></div>
                            </header>

                            <div class="akar-indicator-list">
                                @foreach ($dim['indikator'] as $ind)
                                    @php
                                        $label = $ind['label_capaian'];
                                        $statusClass = match ($label) {
                                            'Baik' => 'baik',
                                            'Sedang' => 'sedang',
                                            'Kurang' => 'kurang',
                                            default => 'kosong',
                                        };

                                        $perubahan = $ind['perubahan_nilai'] ?? 'Tidak Tersedia';
                                        $arah = match ($perubahan) {
                                            'Naik' => ['ikon' => '↗', 'class' => 'naik'],
                                            'Turun' => ['ikon' => '↘', 'class' => 'turun'],
                                            'Tidak berubah' => ['ikon' => '→', 'class' => 'tetap'],
                                            default => ['ikon' => '—', 'class' => 'kosong'],
                                        };
                                    @endphp

                                    <div class="akar-indicator-row">
                                        <div class="akar-indicator-row__number tabular">{{ $ind['nomor'] }}</div>

                                        <div class="akar-indicator-row__name">
                                            <strong>{{ $ind['nama'] }}</strong>
                                            <span>{{ $ind['ambang']['merah'] ?? 'Ambang perhatian tidak tersedia.' }}</span>
                                        </div>

                                        <div class="akar-indicator-row__status">
                                            <span class="akar-status akar-status--{{ $statusClass }}">
                                                <span class="akar-status__dot" aria-hidden="true"></span>
                                                {{ $label }}
                                            </span>
                                        </div>

                                        <div class="akar-indicator-row__trend">
                                            <span class="akar-trend akar-trend--{{ $arah['class'] }}" title="Perubahan: {{ $perubahan }}">
                                                <span aria-hidden="true">{{ $arah['ikon'] }}</span>
                                                {{ $perubahan }}
                                            </span>
                                        </div>

                                        <div class="akar-indicator-row__arrow" aria-hidden="true">→</div>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            @if (! empty($profil['tidak_tersedia']))
                <section class="akar-section akar-section--unavailable akar-reveal" aria-labelledby="judul-tidak-tersedia">
                    <div class="akar-section__heading">
                        <span class="akar-section__index">04</span>
                        <div>
                            <p class="akar-kicker">Catatan data</p>
                            <h2 id="judul-tidak-tersedia" class="akar-section__title">Indikator tanpa data.</h2>
                        </div>
                        <p class="akar-section__aside">Ketiadaan data bukan berarti nilai nol.</p>
                    </div>

                    <details class="akar-unavailable">
                        <summary>
                            <span>{{ count($profil['tidak_tersedia']) }} indikator tidak diukur untuk kombinasi ini</span>
                            <span class="akar-unavailable__toggle" aria-hidden="true">+</span>
                        </summary>

                        <div class="akar-unavailable__grid">
                            @foreach ($profil['tidak_tersedia'] as $ind)
                                <div class="akar-unavailable__item">
                                    <span class="tabular">{{ $ind['nomor'] }}</span>
                                    <strong>{{ $ind['nama'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </details>
                </section>
            @endif

            {{-- Jembatan ke area sekolah: sekolah di kabupaten ini yang sudah
                 mengunggah berkasnya sendiri. Dua area tetap terpisah; ini
                 hanya tautan konteks. --}}
            @if ($this->sekolahDiWilayah->isNotEmpty())
                <div class="akar-chart-wrap">
                    <div class="mb-3 flex items-center gap-3">
                        <h3 class="text-[13px] font-semibold uppercase tracking-[0.05em] text-teks-700">
                            Sekolah di wilayah ini yang sudah mengunggah berkas
                        </h3>
                        <span class="h-px flex-1 bg-krem-300"></span>
                    </div>
                    <p class="mb-3 text-[12px] leading-relaxed text-teks-500">
                        {{ $this->sekolahDiWilayah->count() }} sekolah telah mengunggah Rapor Pendidikan
                        satuan pendidikannya. Capaian tiap sekolah dapat dibuka baca-saja sebagai konteks.
                    </p>
                    <div class="flex flex-col gap-2">
                        @foreach ($this->sekolahDiWilayah as $s)
                            <a href="{{ route('dinas.sekolah', ['kabkota' => $wilayahId, 'wilayah' => $s['wilayah_id']]) }}"
                               wire:navigate
                               class="flex items-center justify-between gap-4 rounded-md border border-krem-300 bg-kartu px-3.5 py-2.5 hover:border-biru-700">
                                <span>
                                    <span class="text-[13px] font-semibold text-teks-900">{{ $s['nama'] }}</span>
                                    <span class="text-[11px] text-teks-500"> · {{ $s['jenis_satuan'] }} · {{ $s['tahun'] }}</span>
                                </span>
                                <span class="flex shrink-0 items-center gap-3 text-[11px] tabular">
                                    <span class="text-kurang">{{ $s['merah'] }}</span>
                                    <span class="text-sedang">{{ $s['kuning'] }}</span>
                                    <span class="text-baik">{{ $s['hijau'] }}</span>
                                    <span class="text-teks-400" aria-hidden="true">→</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>

    <x-grafik-skrip />
</div>

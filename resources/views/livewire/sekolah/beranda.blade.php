<div class="sekolah-beranda-v2">
    @php
        $sekolah = $this->sekolah;
        $kombinasi = $this->kombinasi;
        $ringkasan = $this->ringkasan;
    @endphp

    @if ($sekolah === null)
        <section class="sekolah-beranda-v2__hero">
            <div class="sekolah-beranda-v2__hero-grid">
                <div>
                    <div class="sekolah-beranda-v2__eyebrow">
                        <span>AKAR / SATUAN PENDIDIKAN</span>
                        <span class="sekolah-beranda-v2__eyebrow-line"></span>
                    </div>

                    <h1 class="sekolah-beranda-v2__display-title">
                        Dari data sekolah,
                        <span>menuju tindakan.</span>
                    </h1>

                    <p class="sekolah-beranda-v2__lead">
                        Ruang kerja kepala sekolah dan tim kurikulum untuk menerjemahkan
                        Rapor Pendidikan menjadi prioritas yang jelas dan Rencana Kerja
                        Tahunan berbasis data.
                    </p>
                </div>

                <aside class="sekolah-beranda-v2__hero-aside">
                    <span class="sekolah-beranda-v2__kicker">MULAI DI SINI</span>
                    <strong>Rapor Pendidikan<br>sekolah.</strong>
                    <p>
                        Unggah data satuan pendidikan untuk membuka profil capaian,
                        prioritas, akar masalah, dan rencana kerja.
                    </p>
                    <a class="sekolah-beranda-v2__primary-link" href="{{ route('sekolah.unggah') }}">
                        <span>Unggah berkas</span>
                        <span aria-hidden="true">↗</span>
                    </a>
                </aside>
            </div>
        </section>

        <section class="sekolah-beranda-v2__section sekolah-beranda-v2__section--intro">
            <div class="sekolah-beranda-v2__section-heading">
                <span class="sekolah-beranda-v2__index">01</span>
                <div>
                    <span class="sekolah-beranda-v2__kicker">LANGKAH AWAL</span>
                    <h2>Mulai dengan satu berkas.</h2>
                </div>
                <p>
                    Unduh berkas Rapor Pendidikan satuan pendidikan dari akun
                    belajar.id sekolah Anda, lalu unggah ke AKAR.
                </p>
            </div>

            <div class="sekolah-beranda-v2__upload-row">
                <div class="sekolah-beranda-v2__upload-mark">01</div>
                <div class="sekolah-beranda-v2__upload-copy">
                    <span class="sekolah-beranda-v2__kicker">RAPOR PENDIDIKAN</span>
                    <h3>Siapkan data sekolah untuk dibaca bersama.</h3>
                    <p>
                        Sistem akan menggunakan data yang tersedia untuk menyusun
                        gambaran kondisi sekolah secara lebih terarah.
                    </p>
                </div>
                <div class="sekolah-beranda-v2__upload-action">
                    <span>.xlsx · maksimal 25 MB</span>
                    <a href="{{ route('sekolah.unggah') }}">Mulai unggah <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </section>

        <section class="sekolah-beranda-v2__section sekolah-beranda-v2__section--flow" id="alur">
            <div class="sekolah-beranda-v2__section-heading">
                <span class="sekolah-beranda-v2__index">02</span>
                <div>
                    <span class="sekolah-beranda-v2__kicker">ALUR AKAR</span>
                    <h2>Empat langkah dari data menuju rencana.</h2>
                </div>
                <p>
                    Setiap tahap membawa hasil dari tahap sebelumnya agar pembacaan
                    Rapor Pendidikan tidak berhenti pada angka.
                </p>
            </div>

            <div class="sekolah-beranda-v2__flow">
                <a class="sekolah-beranda-v2__flow-item" href="{{ route('sekolah.unggah') }}">
                    <span class="sekolah-beranda-v2__flow-number">01</span>
                    <strong>Unggah data</strong>
                    <span>Masukkan Rapor Pendidikan sekolah.</span>
                </a>

                <a class="sekolah-beranda-v2__flow-item" href="{{ route('sekolah.profil') }}">
                    <span class="sekolah-beranda-v2__flow-number">02</span>
                    <strong>Pahami capaian</strong>
                    <span>Baca kondisi indikator menurut dimensinya.</span>
                </a>

                <a class="sekolah-beranda-v2__flow-item" href="{{ route('sekolah.prioritas') }}">
                    <span class="sekolah-beranda-v2__flow-number">03</span>
                    <strong>Temukan prioritas</strong>
                    <span>Kenali masalah dan dugaan akar penyebabnya.</span>
                </a>

                <a class="sekolah-beranda-v2__flow-item" href="{{ route('sekolah.rkt') }}">
                    <span class="sekolah-beranda-v2__flow-number">04</span>
                    <strong>Susun rencana</strong>
                    <span>Terjemahkan temuan menjadi Rencana Kerja Tahunan.</span>
                </a>
            </div>
        </section>
    @else
        <section class="sekolah-beranda-v2__hero sekolah-beranda-v2__hero--ready">
            <div class="sekolah-beranda-v2__hero-grid">
                <div>
                    <div class="sekolah-beranda-v2__eyebrow">
                        <span>AKAR / SATUAN PENDIDIKAN</span>
                        <span class="sekolah-beranda-v2__eyebrow-line"></span>
                    </div>

                    <h1 class="sekolah-beranda-v2__display-title">
                        Kenali kondisi sekolah,
                        <span>tentukan apa yang perlu diperbaiki.</span>
                    </h1>

                    <p class="sekolah-beranda-v2__lead">
                        Rapor Pendidikan menjadi titik awal untuk membaca capaian,
                        memilih prioritas, menemukan akar masalah, dan menyusun
                        rencana kerja yang lebih terarah.
                    </p>
                </div>

                <aside class="sekolah-beranda-v2__hero-aside">
                    <span class="sekolah-beranda-v2__kicker">PROFIL SEKOLAH</span>
                    <strong>{{ $sekolah->nama_satuan }}</strong>

                    <p class="sekolah-beranda-v2__context">
                        @if ($sekolah->kabupaten_kota){{ $sekolah->kabupaten_kota }}@endif
                        @if ($sekolah->provinsi) · {{ $sekolah->provinsi }}@endif
                        @if ($kombinasi) · {{ $kombinasi['jenis_satuan'] }} · edisi {{ $kombinasi['tahun'] }}@endif
                    </p>

                    @if ($this->impor?->diproses_pada)
                        <span class="sekolah-beranda-v2__uploaded">
                            Data terakhir diunggah {{ $this->impor->diproses_pada->translatedFormat('d F Y') }}
                        </span>
                    @endif

                    <div class="sekolah-beranda-v2__hero-actions">
                        <a class="sekolah-beranda-v2__primary-link" href="{{ route('sekolah.profil') }}">
                            <span>Lihat profil capaian</span>
                            <span aria-hidden="true">→</span>
                        </a>
                        <a class="sekolah-beranda-v2__text-link" href="{{ route('sekolah.unggah') }}">
                            Unggah berkas baru
                        </a>
                    </div>
                </aside>
            </div>
        </section>

        @if ($ringkasan)
            <section class="sekolah-beranda-v2__summary" aria-label="Ringkasan capaian sekolah">
                <div class="sekolah-beranda-v2__summary-label">
                    <span class="sekolah-beranda-v2__kicker">RINGKASAN</span>
                    <strong>Kondisi indikator<br>sekolah.</strong>
                </div>

                <div class="sekolah-beranda-v2__stats">
                    <div class="sekolah-beranda-v2__stat sekolah-beranda-v2__stat--kurang">
                        <span>01 / Perlu perhatian</span>
                        <strong class="tabular">{{ $ringkasan['merah'] }}</strong>
                    </div>
                    <div class="sekolah-beranda-v2__stat sekolah-beranda-v2__stat--sedang">
                        <span>02 / Cukup</span>
                        <strong class="tabular">{{ $ringkasan['kuning'] }}</strong>
                    </div>
                    <div class="sekolah-beranda-v2__stat sekolah-beranda-v2__stat--baik">
                        <span>03 / Baik</span>
                        <strong class="tabular">{{ $ringkasan['hijau'] }}</strong>
                    </div>
                    <div class="sekolah-beranda-v2__stat sekolah-beranda-v2__stat--kosong">
                        <span>04 / Tidak tersedia</span>
                        <strong class="tabular">{{ $ringkasan['tidak_tersedia'] }}</strong>
                    </div>
                </div>
            </section>
        @else
            <section class="sekolah-beranda-v2__notice">
                <span class="sekolah-beranda-v2__index">!</span>
                <div>
                    <span class="sekolah-beranda-v2__kicker">DATA BELUM TERSEDIA</span>
                    <h2>Berkas sudah diunggah, tetapi belum ada indikator yang dapat dianalisis.</h2>
                    <p>
                        Kombinasi jenjang dan status pada berkas ini belum menghasilkan
                        indikator yang dapat dibaca oleh AKAR.
                    </p>
                </div>
            </section>
        @endif

        <section class="sekolah-beranda-v2__section sekolah-beranda-v2__section--work">
            <div class="sekolah-beranda-v2__section-heading">
                <span class="sekolah-beranda-v2__index">01</span>
                <div>
                    <span class="sekolah-beranda-v2__kicker">RUANG KERJA</span>
                    <h2>Dari capaian, menuju keputusan.</h2>
                </div>
                <p>
                    Pilih tahap yang ingin dibaca. Setiap ruang kerja menggunakan
                    data Rapor Pendidikan yang sudah tersedia di sekolah.
                </p>
            </div>

            <div class="sekolah-beranda-v2__work-list">
                <a class="sekolah-beranda-v2__work-item" href="{{ route('sekolah.profil') }}">
                    <span class="sekolah-beranda-v2__work-number">01</span>
                    <div class="sekolah-beranda-v2__work-main">
                        <span class="sekolah-beranda-v2__kicker">BACA DATA</span>
                        <h3>Profil capaian</h3>
                        <p>Seluruh indikator sekolah, dikelompokkan menurut dimensi.</p>
                    </div>
                    <span class="sekolah-beranda-v2__work-arrow" aria-hidden="true">↗</span>
                </a>

                <a class="sekolah-beranda-v2__work-item" href="{{ route('sekolah.prioritas') }}">
                    <span class="sekolah-beranda-v2__work-number">02</span>
                    <div class="sekolah-beranda-v2__work-main">
                        <span class="sekolah-beranda-v2__kicker">TENTUKAN FOKUS</span>
                        <h3>Prioritas &amp; akar masalah</h3>
                        <p>Indikator paling mendesak beserta dugaan penyebabnya.</p>
                    </div>
                    <span class="sekolah-beranda-v2__work-arrow" aria-hidden="true">↗</span>
                </a>

                <a class="sekolah-beranda-v2__work-item" href="{{ route('sekolah.rkt') }}">
                    <span class="sekolah-beranda-v2__work-number">03</span>
                    <div class="sekolah-beranda-v2__work-main">
                        <span class="sekolah-beranda-v2__kicker">AMBIL TINDAKAN</span>
                        <h3>Rencana Kerja Tahunan</h3>
                        <p>Draf RKT berbasis data, siap disunting dan diunduh.</p>
                    </div>
                    <span class="sekolah-beranda-v2__work-arrow" aria-hidden="true">↗</span>
                </a>
            </div>
        </section>

        <section class="sekolah-beranda-v2__section sekolah-beranda-v2__section--flow">
            <div class="sekolah-beranda-v2__section-heading">
                <span class="sekolah-beranda-v2__index">02</span>
                <div>
                    <span class="sekolah-beranda-v2__kicker">ALUR AKAR</span>
                    <h2>Data yang dibaca sampai menjadi rencana.</h2>
                </div>
                <p>
                    Ikuti alur kerja sekolah dari pemahaman capaian sampai
                    perencanaan tindakan.
                </p>
            </div>

            <div class="sekolah-beranda-v2__flow sekolah-beranda-v2__flow--ready">
                <a class="sekolah-beranda-v2__flow-item" href="{{ route('sekolah.profil') }}">
                    <span class="sekolah-beranda-v2__flow-number">01</span>
                    <strong>Pahami capaian</strong>
                    <span>Mulai dari kondisi indikator sekolah.</span>
                </a>
                <a class="sekolah-beranda-v2__flow-item" href="{{ route('sekolah.prioritas') }}">
                    <span class="sekolah-beranda-v2__flow-number">02</span>
                    <strong>Temukan prioritas</strong>
                    <span>Pilih area yang paling membutuhkan perhatian.</span>
                </a>
                <a class="sekolah-beranda-v2__flow-item" href="{{ route('sekolah.rkt') }}">
                    <span class="sekolah-beranda-v2__flow-number">03</span>
                    <strong>Susun rencana</strong>
                    <span>Turunkan prioritas menjadi kegiatan yang terarah.</span>
                </a>
            </div>
        </section>
    @endif
</div>

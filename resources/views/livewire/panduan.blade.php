@php
    // Daftar isi: [anchor, judul]. Dipakai untuk navigasi samping dan tautan
    // dari halaman alat (mis. route('panduan').'#prioritas').
    $bagian = [
        ['alur', 'Alur kerja'],
        ['profil', 'Membaca profil capaian'],
        ['prioritas', 'Skor prioritas'],
        ['akar', 'Akar masalah & tingkat keyakinan'],
        ['banding', 'Perbandingan antardaerah'],
        ['tren', 'Tren lintas tahun'],
        ['rencana', 'Rencana tindak lanjut & ekspor'],
        ['impor', 'Impor berkas'],
        ['sumber', 'Sumber data & batasan'],
    ];

    $kb = $bobot['label'] ?? 40;
    $kp = $bobot['perubahan'] ?? 25;
    $ko = $bobot['posisi'] ?? 20;
    $kt = $bobot['turunan'] ?? 15;
@endphp

<div class="flex flex-col gap-6">
    <x-kepala-halaman
        judul="Panduan penggunaan AKAR"
        lead="Cara membaca setiap halaman, arti label dan skor, serta batasan yang perlu diketahui sebelum memakai hasilnya untuk perencanaan." />

    <div class="grid gap-6 lg:grid-cols-[200px_1fr]">
        {{-- Daftar isi lengket --}}
        <nav class="hidden lg:block">
            <div class="sticky top-20 flex flex-col gap-1 text-[13px]">
                <span class="px-2 pb-1 text-[11px] font-semibold uppercase tracking-[0.05em] text-teks-400">Isi panduan</span>
                @foreach ($bagian as [$anchor, $judul])
                    <a href="#{{ $anchor }}"
                       class="rounded px-2 py-1.5 text-teks-700 hover:bg-krem-200 hover:text-teks-900">{{ $judul }}</a>
                @endforeach
            </div>
        </nav>

        <div class="flex flex-col gap-5">
            {{-- ALUR --}}
            <section id="alur" class="scroll-mt-20">
                <x-kartu judul="Alur kerja">
                    <p class="text-[13.5px] leading-relaxed text-teks-700">
                        AKAR membaca berkas Rapor Pendidikan, menemukan indikator yang bermasalah,
                        menelusuri kemungkinan akar masalahnya, lalu membantu menyusun rencana tindak lanjut.
                        Urutan pemakaian yang disarankan:
                    </p>
                    <ol class="mt-4 flex flex-col gap-3">
                        @foreach ([
                            ['Profil capaian', 'Lihat kondisi seluruh indikator satu wilayah pada satu jenjang, dikelompokkan menurut dimensi.'],
                            ['Prioritas & akar masalah', 'Jalankan analisis untuk mengurutkan indikator bermasalah menurut skor prioritas, lalu telusuri akar masalah tiap indikator.'],
                            ['Perbandingan antardaerah', 'Lihat posisi wilayah terhadap kabupaten/kota lain di provinsi yang sama, beserta pembanding agregat provinsi dan nasional.'],
                            ['Tren lintas tahun', 'Bila tersedia lebih dari satu edisi Rapor Pendidikan, lihat indikator mana yang memburuk atau membaik dari tahun ke tahun.'],
                            ['Rencana tindak lanjut', 'Susun draf rencana dari usulan kegiatan, sunting sesuai kebutuhan, lalu ekspor ke PDF atau Excel untuk dibawa ke forum perencanaan.'],
                        ] as $i => [$judul, $isi])
                            <li class="flex gap-3">
                                <span class="grid size-6 shrink-0 place-items-center rounded-full bg-biru-700 text-[11px] font-semibold text-white">{{ $i + 1 }}</span>
                                <div>
                                    <span class="text-[13.5px] font-semibold text-teks-900">{{ $judul }}</span>
                                    <p class="text-[13px] leading-relaxed text-teks-700">{{ $isi }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </x-kartu>
            </section>

            {{-- PROFIL --}}
            <section id="profil" class="scroll-mt-20">
                <x-kartu judul="Membaca profil capaian">
                    <p class="text-[13.5px] leading-relaxed text-teks-700">
                        Setiap indikator berlabel salah satu dari empat kondisi. Ambang tiap label
                        ditetapkan resmi oleh Kemendikdasmen di berkas metadata, bukan disusun oleh tim.
                    </p>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-[13px]">
                            <tbody>
                                @foreach ([
                                    ['Baik', 'Sebagian besar sudah mencapai batas minimum. Pertahankan praktik yang berjalan.'],
                                    ['Sedang', 'Sebagian sudah mencapai batas minimum, tetapi perlu didorong lebih banyak.'],
                                    ['Kurang', 'Belum mencapai batas minimum. Perlu upaya khusus, kandidat prioritas.'],
                                    ['Tidak Tersedia', 'Indikator ini tidak diukur untuk kombinasi wilayah/jenjang tersebut. Bukan nilai nol — memang tidak ada datanya di berkas sumber.'],
                                ] as [$label, $arti])
                                    <tr class="border-b border-krem-300 last:border-0">
                                        <td class="w-40 py-2.5 pr-4 align-top"><x-badge-capaian :label="$label" /></td>
                                        <td class="py-2.5 leading-relaxed text-teks-700">{{ $arti }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-4 text-[12px] leading-relaxed text-teks-500">
                        Kolom "perubahan" menunjukkan arah dibanding tahun sebelumnya: <x-arah-perubahan nilai="Naik" />,
                        <x-arah-perubahan nilai="Turun" />, atau <x-arah-perubahan nilai="Tidak berubah" />.
                        Grafik "Sebaran per dimensi" merangkum jumlah indikator tiap kondisi untuk setiap dimensi A–E.
                    </p>
                </x-kartu>
            </section>

            {{-- PRIORITAS --}}
            <section id="prioritas" class="scroll-mt-20">
                <x-kartu judul="Skor prioritas">
                    <p class="text-[13.5px] leading-relaxed text-teks-700">
                        Skor prioritas mengurutkan indikator bermasalah dari yang paling mendesak.
                        Rentang 0–100. Setiap skor dapat ditelusuri ke empat komponen pembentuknya
                        (buka "Rincian skor" pada tiap kartu indikator):
                    </p>
                    <div class="mt-4 flex flex-col gap-2.5">
                        @foreach ([
                            ["Label capaian ($kb%)", 'Kurang memberi kontribusi penuh, Sedang setengah, Baik nol.'],
                            ["Arah perubahan ($kp%)", 'Menurun memberi kontribusi penuh, tidak berubah setengah, membaik nol.'],
                            ["Posisi relatif ($ko%)", 'Seberapa tertinggal wilayah ini dibanding kabupaten/kota lain di provinsinya pada indikator yang sama.'],
                            ["Dampak ke indikator turunan ($kt%)", 'Proporsi indikator anak yang ikut berlabel Kurang atau Sedang.'],
                        ] as [$judul, $isi])
                            <div class="flex gap-3">
                                <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-biru-700"></span>
                                <div>
                                    <span class="text-[13px] font-semibold text-teks-900">{{ $judul }}</span>
                                    <p class="text-[13px] leading-relaxed text-teks-700">{{ $isi }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-4 text-[12px] leading-relaxed text-teks-500">
                        Bobot dibaca dari <code class="rounded bg-krem-200 px-1 py-0.5 text-[11px]">config/akar.php</code>
                        dan disalin ke setiap analisis, sehingga hasil lama tetap dapat direproduksi bila bobot diubah.
                        Indikator berlabel "Tidak Tersedia" dikecualikan dari perhitungan, bukan diberi skor nol.
                    </p>
                </x-kartu>
            </section>

            {{-- AKAR --}}
            <section id="akar" class="scroll-mt-20">
                <x-kartu judul="Akar masalah & tingkat keyakinan">
                    <p class="text-[13.5px] leading-relaxed text-teks-700">
                        Untuk 15–20 indikator prioritas, AKAR memeriksa indikator pendukung yang secara
                        konseptual dapat menjelaskan masalah (pohon keputusan di
                        <code class="rounded bg-krem-200 px-1 py-0.5 text-[11px]">config/intervensi.php</code>),
                        mengumpulkan buktinya, lalu menetapkan tingkat keyakinan:
                    </p>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-[13px]">
                            <tbody>
                                @foreach ([
                                    ['Bukti kuat', 'Dua atau lebih indikator pendukung berlabel Kurang.'],
                                    ['Bukti sedang', 'Satu indikator pendukung berlabel Kurang, atau dua berlabel Sedang.'],
                                    ['Bukti lemah', 'Satu indikator pendukung berlabel Sedang.'],
                                    ['Bukti belum cukup', 'Seluruh indikator pendukung berlabel Baik atau tidak tersedia. Kesimpulan tidak dipaksakan.'],
                                ] as [$tingkat, $syarat])
                                    <tr class="border-b border-krem-300 last:border-0">
                                        <td class="w-36 py-2.5 pr-4 align-top text-[13px] font-semibold text-teks-900">{{ $tingkat }}</td>
                                        <td class="py-2.5 leading-relaxed text-teks-700">{{ $syarat }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-4 text-[12px] leading-relaxed text-teks-500">
                        Untuk indikator di luar 15–20 yang dipetakan, halaman menampilkan capaiannya
                        tetapi menyatakan "rekomendasi akar masalah belum tersedia". AKAR tidak mengarang rekomendasi.
                    </p>
                </x-kartu>
            </section>

            {{-- BANDING --}}
            <section id="banding" class="scroll-mt-20">
                <x-kartu judul="Perbandingan antardaerah">
                    <p class="text-[13.5px] leading-relaxed text-teks-700">
                        Peringkat dihitung terhadap seluruh kabupaten/kota di provinsi yang sama, pada
                        indikator, tahun, jenjang, dan status satuan yang sama. Urutan mutu label:
                        Baik lebih tinggi daripada Sedang, Sedang lebih tinggi daripada Kurang.
                        Daerah tanpa data pada indikator itu dikeluarkan dari populasi pemeringkatan,
                        bukan ditempatkan di posisi terbawah.
                    </p>
                    <p class="mt-3 text-[13px] leading-relaxed text-teks-700">
                        Karena banyak daerah dapat berbagi label yang sama, peringkat sering berupa rentang
                        (mis. "8–38 dari 38"). Grafik batang menampilkan seluruh daerah sekaligus; daerah
                        yang dipilih ditandai garis tebal.
                    </p>
                </x-kartu>
            </section>

            {{-- TREN --}}
            <section id="tren" class="scroll-mt-20">
                <x-kartu judul="Tren lintas tahun">
                    <p class="text-[13.5px] leading-relaxed text-teks-700">
                        Analisis tren memerlukan minimal dua edisi Rapor Pendidikan. Grafik hanya
                        menampilkan indikator yang bergerak — memburuk berturut-turut atau membaik
                        konsisten — karena garis datar dari indikator stabil hanya menambah keramaian.
                        Indikator stabil ditarik putus-putus bila hampir tidak ada gerakan.
                    </p>
                </x-kartu>
            </section>

            {{-- RENCANA --}}
            <section id="rencana" class="scroll-mt-20">
                <x-kartu judul="Rencana tindak lanjut & ekspor">
                    <p class="text-[13.5px] leading-relaxed text-teks-700">
                        Draf rencana disusun otomatis dari katalog kegiatan
                        (<code class="rounded bg-krem-200 px-1 py-0.5 text-[11px]">config/kegiatan.php</code>)
                        untuk indikator prioritas yang akar masalahnya berbukti cukup. Tabel dapat disunting
                        langsung: ubah teks, tambah baris, atau hapus baris. Hasilnya diekspor ke PDF (untuk
                        dilampirkan pada dokumen perencanaan) atau Excel (untuk diolah lebih lanjut).
                    </p>
                    <p class="mt-3 text-[12px] leading-relaxed text-teks-500">
                        Pada mode satuan pendidikan, halaman ini disebut Rencana Kerja Tahunan (RKT).
                    </p>
                </x-kartu>
            </section>

            {{-- IMPOR --}}
            <section id="impor" class="scroll-mt-20">
                <x-kartu judul="Impor berkas">
                    <p class="text-[13.5px] leading-relaxed text-teks-700">
                        Berkas Rapor Pendidikan tingkat daerah berukuran besar (16–21 MB, 38 sheet provinsi),
                        jadi parsingnya dilakukan di mesin lokal lewat perintah
                        <code class="rounded bg-krem-200 px-1 py-0.5 text-[11px]">php artisan akar:impor</code>,
                        bukan diunggah lewat aplikasi. Server hanya menyajikan analisis. Halaman "Impor berkas"
                        menampilkan riwayat dan status tiap berkas yang pernah diproses.
                    </p>
                    <p class="mt-3 text-[13px] leading-relaxed text-teks-700">
                        Berkas tingkat satuan pendidikan jauh lebih kecil dan diunggah langsung oleh
                        kepala sekolah melalui menu "Mode satuan pendidikan".
                    </p>
                </x-kartu>
            </section>

            {{-- SUMBER --}}
            <section id="sumber" class="scroll-mt-20">
                <x-kartu judul="Sumber data & batasan">
                    <ul class="flex flex-col gap-2.5 text-[13px] leading-relaxed text-teks-700">
                        <li class="flex gap-2.5"><span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-biru-700"></span>
                            Sumber: Kementerian Pendidikan Dasar dan Menengah, Data Rapor Pendidikan Indonesia,
                            Portal Satu Data Kemendikdasmen.</li>
                        <li class="flex gap-2.5"><span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-biru-700"></span>
                            Seluruh analisis berbasis aturan, bukan pembelajaran mesin. Keluaran dipakai untuk
                            perencanaan anggaran publik, jadi harus dapat dijelaskan sepenuhnya.</li>
                        <li class="flex gap-2.5"><span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-biru-700"></span>
                            "Tidak Tersedia" berarti tidak ada data pada berkas sumber untuk kombinasi tersebut,
                            bukan capaian nol. Ditampilkan terpisah dari indikator yang bermasalah.</li>
                        <li class="flex gap-2.5"><span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-biru-700"></span>
                            Mode satuan pendidikan telah dibangun dengan validasi struktur berkas, namun
                            belum diuji dengan berkas Rapor Pendidikan satuan pendidikan yang asli.</li>
                        <li class="flex gap-2.5"><span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-biru-700"></span>
                            Seluruh data bersifat agregat wilayah atau satuan pendidikan. Tidak ada data
                            pribadi siswa, guru, atau tenaga kependidikan.</li>
                    </ul>
                </x-kartu>
            </section>
        </div>
    </div>
</div>

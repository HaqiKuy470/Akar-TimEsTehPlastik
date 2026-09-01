@php
    /**
     * Laporan AKAR untuk dicetak dan dibawa ke rapat perencanaan.
     * DESIGN.md bagian 8: latar putih murni (bukan krem), warna status tetap
     * dipakai namun selalu disertai ikon dan teks agar terbaca hitam putih.
     * Tanpa Tailwind — dompdf tidak menjalankan proses build.
     */
    $ikonCapaian = ['Baik' => '(+)', 'Sedang' => '(~)', 'Kurang' => '(-)', 'Tidak Tersedia' => '(x)'];
    $warnaCapaian = ['Baik' => '#1b5e36', 'Sedang' => '#7a4e00', 'Kurang' => '#95201f', 'Tidak Tersedia' => '#615a4e'];
    $badge = function (string $label) use ($ikonCapaian, $warnaCapaian) {
        $warna = $warnaCapaian[$label] ?? '#615a4e';
        $ikon = $ikonCapaian[$label] ?? '(x)';

        return '<span style="border:0.75pt solid '.$warna.';color:'.$warna.';padding:0 3pt;font-size:8pt;white-space:nowrap;">'.$ikon.' '.e($label).'</span>';
    };
    $angka = fn (float $n) => rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 2cm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.45;
            color: #1a1a1a;
            background: #ffffff;
        }
        h1 { font-size: 16pt; margin: 0 0 2pt; }
        h2 { font-size: 12pt; margin: 18pt 0 6pt; border-bottom: 1pt solid #333; padding-bottom: 2pt; }
        h3 { font-size: 10.5pt; margin: 10pt 0 3pt; }
        p { margin: 0 0 4pt; }
        .kepala { border-bottom: 2pt solid #0b2545; padding-bottom: 6pt; margin-bottom: 4pt; }
        .kepala .meta { font-size: 9pt; color: #444; }
        table { width: 100%; border-collapse: collapse; margin: 4pt 0 8pt; }
        th, td { border: 0.75pt solid #999; padding: 3pt 5pt; text-align: left; vertical-align: top; font-size: 9pt; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th { background: #ebe5d6; font-size: 8.5pt; text-transform: uppercase; }
        .num { text-align: right; }
        .ringkas td { border: none; padding: 2pt 10pt 2pt 0; }
        .bar-wrap { background: #eee; width: 100%; height: 8pt; }
        .bar { background: #14448c; height: 8pt; }
        .kartu { border: 0.75pt solid #999; padding: 6pt 8pt; margin-bottom: 6pt; page-break-inside: avoid; }
        .kandidat { border-left: 2pt solid #999; padding-left: 6pt; margin: 4pt 0; }
        .footer { position: fixed; bottom: -1.4cm; left: 0; right: 0; font-size: 7.5pt; color: #555; border-top: 0.5pt solid #ccc; padding-top: 3pt; }
        .footer .hal:after { content: counter(page) " / " counter(pages); }
        .muted { color: #666; }
        .tt { font-style: italic; color: #666; }
    </style>
</head>
<body>
    <div class="footer">
        Sumber: Kementerian Pendidikan Dasar dan Menengah, Data Rapor Pendidikan Indonesia, diakses {{ $tanggal_cetak }}.
        &nbsp;&middot;&nbsp; Disusun dengan AKAR.
        &nbsp;&middot;&nbsp; <span class="hal"></span>
    </div>

    <div class="kepala">
        <h1>Laporan Analisis Kausal dan Rekomendasi</h1>
        <div class="meta">
            {{ $wilayah }} &middot; Jenjang {{ $jenjang }} &middot; {{ $status }} &middot;
            Data tahun {{ $tahun }} &middot; Dicetak {{ $tanggal_cetak }}
        </div>
    </div>
    <h2>1. Profil capaian</h2>
    @if (! ($profil['tersedia'] ?? false))
        <p class="muted">Data profil capaian tidak tersedia untuk kombinasi ini.</p>
    @else
        <table class="ringkas">
            <tr>
                <td><strong>{{ $profil['ringkasan']['merah'] }}</strong> indikator perlu perhatian</td>
                <td><strong>{{ $profil['ringkasan']['kuning'] }}</strong> cukup</td>
                <td><strong>{{ $profil['ringkasan']['hijau'] }}</strong> baik</td>
                <td><strong>{{ $profil['ringkasan']['tidak_tersedia'] }}</strong> tidak tersedia</td>
                <td>{{ $profil['ringkasan']['total'] }} indikator diukur</td>
            </tr>
        </table>

        @foreach ($profil['dimensi'] as $dim)
            <h3>{{ $dim['kode'] }}. {{ $dim['nama'] }}</h3>
            <table>
                <thead>
                    <tr><th style="width:55%">Indikator</th><th style="width:20%">Capaian</th><th style="width:25%">Perubahan</th></tr>
                </thead>
                <tbody>
                    @foreach ($dim['indikator'] as $ind)
                        <tr>
                            <td>{{ $ind['nomor'] }} {{ $ind['nama'] }}</td>
                            <td>{!! $badge($ind['label_capaian']) !!}</td>
                            <td>{{ $ind['perubahan_nilai'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

        @if (! empty($profil['tidak_tersedia']))
            <p class="tt">
                Tidak diukur di level kabupaten/kota ({{ count($profil['tidak_tersedia']) }}):
                {{ collect($profil['tidak_tersedia'])->map(fn ($i) => $i['nomor'])->implode(', ') }}.
                Ini ketiadaan data pada berkas sumber, bukan nilai nol.
            </p>
        @endif
    @endif
    <h2>2. Indikator prioritas dan akar masalah</h2>
    @forelse ($prioritas as $p)
        <div class="kartu">
            <h3>{{ $p['peringkat'] }}. {{ $p['nomor'] }} {{ $p['nama'] }} &mdash; skor {{ $angka($p['skor']) }} dari 100</h3>
            <p>{!! $badge($p['label']) !!} &nbsp; {{ $p['kalimat_penjelas'] }}</p>

            @if (! empty($p['komponen_skor']))
                <table>
                    <thead>
                        <tr><th style="width:40%">Komponen skor</th><th style="width:45%">Kontribusi</th><th style="width:15%" class="num">Nilai</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($p['komponen_skor'] as $k)
                            <tr>
                                <td>{{ $k['nama'] }}</td>
                                <td>
                                    <div class="bar-wrap">
                                        <div class="bar" style="width: {{ $k['bobot_maks'] > 0 ? round($k['kontribusi'] / $k['bobot_maks'] * 100) : 0 }}%;"></div>
                                    </div>
                                </td>
                                <td class="num">{{ $angka($k['kontribusi']) }} / {{ $angka($k['bobot_maks']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if (empty($p['akar']))
                <p class="muted">Rekomendasi akar masalah belum tersedia untuk indikator ini.</p>
            @else
                @foreach ($p['akar'] as $a)
                    <div class="kandidat">
                        <p><strong>{{ $a['label'] }}</strong> &mdash; {{ $a['keyakinan'] }}</p>
                        @if (! empty($a['bukti']))
                            <p class="muted">
                                Bukti:
                                @foreach ($a['bukti'] as $b)
                                    {{ $b['nomor'] }} {{ $b['nama'] }} ({{ $b['label'] }}){{ ! $loop->last ? ';' : '' }}
                                @endforeach
                            </p>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    @empty
        <p class="muted">Tidak ada indikator berlabel Kurang atau Sedang untuk kombinasi ini.</p>
    @endforelse
    <h2>3. Rencana tindak lanjut</h2>
    @if ($rencana === null || $rencana->item->isEmpty())
        <p class="muted">Draf rencana tindak lanjut belum disusun untuk analisis ini.</p>
    @else
        <p><strong>{{ $rencana->judul }}</strong></p>
        <table>
            <thead>
                <tr>
                    <th style="width:18%">Masalah</th>
                    <th style="width:18%">Akar masalah</th>
                    <th style="width:28%">Kegiatan</th>
                    <th style="width:14%">Penanggung jawab</th>
                    <th style="width:14%">Indikator keberhasilan</th>
                    <th style="width:8%">Waktu</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rencana->item as $it)
                    <tr>
                        <td>{{ $it->masalah }}</td>
                        <td>{{ $it->akar_masalah }}</td>
                        <td>{{ $it->kegiatan }}</td>
                        <td>{{ $it->penanggung_jawab }}</td>
                        <td>{{ $it->indikator_keberhasilan }}</td>
                        <td>{{ $it->perkiraan_waktu }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>

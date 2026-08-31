<?php

declare(strict_types=1);

namespace App\Services\Akar\Analysis;

use App\Models\Capaian;
use App\Models\Indikator;
use App\Models\Wilayah;

/**
 * F6 — Analisis Tren Lintas Tahun.
 *
 * Portal Rapor Pendidikan resmi menampilkan capaian satu tahun. Nilai tambah
 * AKAR di sini adalah menampilkan lintasannya: indikator mana yang memburuk dua
 * tahun berturut-turut (perlu perhatian serius) dan mana yang membaik konsisten
 * (praktik yang layak dipertahankan).
 *
 * Seluruh perbandingan antar tahun memakai jenjang mutu label dari
 * config/akar.php (Baik > Sedang > Kurang). Tahun tanpa baris capaian untuk
 * sebuah indikator berarti "Tidak Tersedia" pada tahun itu (baris "Tidak
 * Tersedia" memang tidak disimpan saat impor), dan memutus rangkaian penilaian
 * tren, bukan dianggap penurunan.
 */
class TrenService
{
    /** Nomor kelas seri grafik menurut klasifikasi, memakai warna status DESIGN.md. */
    private const WARNA_KLASIFIKASI = [
        'memburuk_berturut' => '#95201F',  // kurang
        'membaik_konsisten' => '#1B5E36',  // baik
        'stabil' => '#615A4E',             // kosong/netral hangat
    ];

    /**
     * @param  list<int>|null  $tahunList  batasi ke tahun tertentu; null = semua edisi yang memuat jenjang ini
     * @return array<string, mixed>
     */
    public function untukWilayah(Wilayah $wilayah, string $jenisSatuan, string $statusSatuan, ?array $tahunList = null): array
    {
        $tahun = $tahunList !== null
            ? array_values(array_unique(array_map('intval', $tahunList)))
            : $this->tahunEdisi($jenisSatuan, $statusSatuan);
        sort($tahun);

        $rangka = [
            'wilayah' => ['nama' => $wilayah->namaTampilan(), 'level' => $wilayah->level],
            'jenis_satuan' => $jenisSatuan,
            'status_satuan' => $statusSatuan,
            'tahun' => $tahun,
            'cukup_tahun' => count($tahun) >= 2,
            'ringkasan' => ['memburuk_berturut' => 0, 'membaik_konsisten' => 0, 'stabil' => 0, 'total' => 0],
            'dimensi' => [],
            'memburuk' => [],
            'membaik' => [],
            'grafik' => ['tahun' => array_map('strval', $tahun), 'seri' => []],
        ];

        if ($tahun === []) {
            return $rangka;
        }

        $baris = Capaian::query()
            ->where('wilayah_id', $wilayah->id)
            ->where('jenis_satuan', $jenisSatuan)
            ->where('status_satuan', $statusSatuan)
            ->whereIn('tahun', $tahun)
            ->get(['indikator_id', 'tahun', 'label_capaian']);

        if ($baris->isEmpty()) {
            return $rangka;
        }

        /** @var array<int, array<int, string>> $labelPerIndikator [indikator_id][tahun] => label */
        $labelPerIndikator = [];
        foreach ($baris as $b) {
            $labelPerIndikator[$b->indikator_id][$b->tahun] = $b->label_capaian;
        }

        $indikator = Indikator::query()
            ->whereIn('id', array_keys($labelPerIndikator))
            ->get(['id', 'nomor', 'nama', 'dimensi'])
            ->keyBy('id');

        $peringkat = (array) config('akar.peringkat_label', ['Baik' => 3, 'Sedang' => 2, 'Kurang' => 1]);
        $namaDimensi = (array) config('akar.dimensi', []);

        $baris = [];
        foreach ($labelPerIndikator as $indikatorId => $labelTahun) {
            $ind = $indikator->get($indikatorId);
            if ($ind === null) {
                continue;
            }

            $deret = [];
            $nilai = [];
            foreach ($tahun as $t) {
                $label = $labelTahun[$t] ?? 'Tidak Tersedia';
                $deret[$t] = $label;
                $nilai[] = $peringkat[$label] ?? null;
            }

            $klasifikasi = $this->klasifikasikan($nilai);

            $entri = [
                'nomor' => $ind->nomor,
                'nama' => $ind->nama,
                'dimensi' => $ind->dimensi,
                'deret' => $deret,
                'nilai' => $nilai,
                'klasifikasi' => $klasifikasi,
            ];
            $baris[] = $entri;

            $rangka['ringkasan'][$klasifikasi]++;
            $rangka['ringkasan']['total']++;

            $kode = $ind->dimensi;
            $rangka['dimensi'][$kode] ??= [
                'kode' => $kode,
                'nama' => $namaDimensi[$kode] ?? $kode,
                'indikator' => [],
            ];
            $rangka['dimensi'][$kode]['indikator'][] = $entri;
        }

        usort($baris, fn ($a, $b) => $this->kunciUrut($a['nomor']) <=> $this->kunciUrut($b['nomor']));
        ksort($rangka['dimensi']);

        $rangka['memburuk'] = array_values(array_filter($baris, fn ($r) => $r['klasifikasi'] === 'memburuk_berturut'));
        $rangka['membaik'] = array_values(array_filter($baris, fn ($r) => $r['klasifikasi'] === 'membaik_konsisten'));

        $rangka['grafik']['seri'] = $this->seriGrafik($baris);

        return $rangka;
    }

    /**
     * Tahun edisi yang memuat jenjang & status ini, dari data capaian.
     *
     * @return list<int>
     */
    private function tahunEdisi(string $jenisSatuan, string $statusSatuan): array
    {
        return Capaian::query()
            ->where('jenis_satuan', $jenisSatuan)
            ->where('status_satuan', $statusSatuan)
            ->distinct()
            ->orderBy('tahun')
            ->pluck('tahun')
            ->map(fn ($t) => (int) $t)
            ->all();
    }

    /**
     * @param  list<int|null>  $nilai  nilai mutu per tahun terurut; null = Tidak Tersedia
     */
    private function klasifikasikan(array $nilai): string
    {
        $turunBeruntunMaks = 0;
        $turunBeruntun = 0;
        $adaNaik = false;
        $adaTurun = false;
        $pasanganTerbanding = 0;

        for ($i = 1; $i < count($nilai); $i++) {
            $sebelum = $nilai[$i - 1];
            $kini = $nilai[$i];
            if ($sebelum === null || $kini === null) {
                $turunBeruntun = 0;

                continue;
            }
            $pasanganTerbanding++;

            if ($kini < $sebelum) {
                $adaTurun = true;
                $turunBeruntun++;
                $turunBeruntunMaks = max($turunBeruntunMaks, $turunBeruntun);
            } elseif ($kini > $sebelum) {
                $adaNaik = true;
                $turunBeruntun = 0;
            } else {
                $turunBeruntun = 0;
            }
        }

        return match (true) {
            $turunBeruntunMaks >= 2 => 'memburuk_berturut',
            $pasanganTerbanding >= 1 && $adaNaik && ! $adaTurun => 'membaik_konsisten',
            default => 'stabil',
        };
    }

    /**
     * Satu garis per indikator (DESIGN.md 5). Diprioritaskan indikator yang
     * memburuk berturut lalu yang membaik konsisten, dibatasi agar grafik tetap
     * terbaca. Warna garis mengikuti klasifikasi (palet warna status).
     *
     * @param  list<array<string, mixed>>  $baris
     * @return list<array<string, mixed>>
     */
    private function seriGrafik(array $baris): array
    {
        $prioritas = ['memburuk_berturut' => 0, 'membaik_konsisten' => 1, 'stabil' => 2];
        $terurut = $baris;
        usort($terurut, fn ($a, $b) => [$prioritas[$a['klasifikasi']], $this->kunciUrut($a['nomor'])]
            <=> [$prioritas[$b['klasifikasi']], $this->kunciUrut($b['nomor'])]);

        $seri = [];
        foreach (array_slice($terurut, 0, 8) as $r) {
            $seri[] = [
                'nomor' => $r['nomor'],
                'nama' => $r['nama'],
                'nilai' => $r['nilai'],
                'klasifikasi' => $r['klasifikasi'],
                'warna' => self::WARNA_KLASIFIKASI[$r['klasifikasi']],
            ];
        }

        return $seri;
    }

    /**
     * "A.1.10" -> "A.001.010" agar pengurutan tidak menaruh A.1.10 sebelum A.1.2.
     */
    private function kunciUrut(string $nomor): string
    {
        return preg_replace_callback('/\d+/', static fn ($m) => str_pad($m[0], 3, '0', STR_PAD_LEFT), $nomor) ?? $nomor;
    }
}

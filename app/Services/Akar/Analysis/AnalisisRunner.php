<?php

declare(strict_types=1);

namespace App\Services\Akar\Analysis;

use App\Models\Analisis;
use App\Models\Capaian;
use App\Models\Indikator;
use App\Models\Wilayah;
use Illuminate\Support\Facades\DB;

class AnalisisRunner
{
    public function __construct(
        private readonly PrioritasCalculator $kalkulator,
        private readonly PenjelasGenerator $penjelas,
    ) {}

    public function jalankan(
        Wilayah $wilayah,
        int $tahun,
        string $jenisSatuan,
        string $statusSatuan,
        ?int $dibuatOleh = null,
    ): Analisis {
        $labelBermasalah = (array) config('akar.label_bermasalah', ['Kurang', 'Sedang']);
        $peringkatLabel = (array) config('akar.peringkat_label', ['Baik' => 3, 'Sedang' => 2, 'Kurang' => 1]);

        return DB::transaction(function () use (
            $wilayah, $tahun, $jenisSatuan, $statusSatuan, $dibuatOleh, $labelBermasalah, $peringkatLabel
        ) {
            $analisis = Analisis::create([
                'wilayah_id' => $wilayah->id,
                'tahun' => $tahun,
                'jenis_satuan' => $jenisSatuan,
                'status_satuan' => $statusSatuan,
                'bobot_dipakai' => (array) config('akar'),
                'dibuat_oleh' => $dibuatOleh,
            ]);

            $bermasalah = Capaian::query()
                ->where('wilayah_id', $wilayah->id)
                ->where('tahun', $tahun)
                ->where('jenis_satuan', $jenisSatuan)
                ->where('status_satuan', $statusSatuan)
                ->whereIn('label_capaian', $labelBermasalah)
                ->with('indikator:id,nomor,nama,induk_id')
                ->get(['id', 'indikator_id', 'label_capaian', 'perubahan_nilai']);

            if ($bermasalah->isEmpty()) {
                return $analisis->load('prioritas');
            }

            $indikatorIds = $bermasalah->pluck('indikator_id')->all();

            $satuan = $wilayah->level === 'satuan';

            $bobotPosisi = $satuan
                ? $this->hitungPosisiTerhadapKabupaten(
                    $wilayah, $tahun, $jenisSatuan, $statusSatuan, $indikatorIds, $peringkatLabel
                )
                : $this->hitungPosisiRelatif(
                    $wilayah, $tahun, $jenisSatuan, $statusSatuan, $indikatorIds, $peringkatLabel
                );
            $bobotTurunan = $this->hitungDampakTurunan(
                $wilayah, $tahun, $jenisSatuan, $statusSatuan, $indikatorIds, $labelBermasalah
            );

            $baris = $bermasalah->map(function (Capaian $capaian) use ($bobotPosisi, $bobotTurunan, $satuan) {
                $hasil = $this->kalkulator->hitung([
                    'label' => $capaian->label_capaian,
                    'perubahan' => $capaian->perubahan_nilai,
                    'bobot_posisi' => $bobotPosisi[$capaian->indikator_id]['nilai'] ?? 0.0,
                    'bobot_turunan' => $bobotTurunan[$capaian->indikator_id]['nilai'] ?? 0.0,
                ]);

                $posisi = $bobotPosisi[$capaian->indikator_id] ?? null;
                $turunan = $bobotTurunan[$capaian->indikator_id] ?? null;

                if ($satuan) {
                    $hasil['komponen'] = $this->tandaiPosisiSatuan($hasil['komponen'], $posisi);
                }

                $kalimat = $this->penjelas->untuk($hasil['komponen'], [
                    'label' => $capaian->label_capaian,
                    'perubahan' => $capaian->perubahan_nilai,
                    'peringkat' => $posisi['peringkat'] ?? null,
                    'dari' => $posisi['dari'] ?? null,
                    'anak_bermasalah' => $turunan['bermasalah'] ?? null,
                    'anak_total' => $turunan['total'] ?? null,
                    'pembanding_kabupaten' => $satuan ? [
                        'nama' => $posisi['pembanding_nama'] ?? null,
                        'label' => $posisi['pembanding_kab'] ?? null,
                        'tersedia' => (bool) ($posisi['pembanding_tersedia'] ?? false),
                    ] : null,
                ]);

                return [
                    'indikator_id' => $capaian->indikator_id,
                    'skor' => $hasil['skor'],
                    'komponen' => $hasil['komponen'],
                    'kalimat' => $kalimat,
                ];
            })
                ->sortByDesc('skor')
                ->values();

            $sekarang = now();
            $peringkat = 0;
            $sisipan = $baris->map(function (array $b) use ($analisis, &$peringkat, $sekarang) {
                $peringkat++;

                return [
                    'analisis_id' => $analisis->id,
                    'indikator_id' => $b['indikator_id'],
                    'skor' => $b['skor'],
                    'komponen_skor' => json_encode($b['komponen'], JSON_UNESCAPED_UNICODE),
                    'kalimat_penjelas' => $b['kalimat'],
                    'peringkat' => $peringkat,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
            })->all();

            DB::table('analisis_prioritas')->insert($sisipan);

            return $analisis->load(['prioritas' => fn ($q) => $q->orderBy('peringkat'), 'prioritas.indikator']);
        });
    }

    /**
     * @param  list<int>  $indikatorIds
     * @param  array<string, int>  $peringkatLabel
     * @return array<int, array{nilai: float, peringkat: int, dari: int}>
     */
    private function hitungPosisiRelatif(
        Wilayah $wilayah,
        int $tahun,
        string $jenisSatuan,
        string $statusSatuan,
        array $indikatorIds,
        array $peringkatLabel,
    ): array {
        $kabkotaProvinsi = Wilayah::query()
            ->where('level', 'kabkota')
            ->where('provinsi', $wilayah->provinsi)
            ->pluck('id');

        $distribusi = Capaian::query()
            ->selectRaw('indikator_id, label_capaian, COUNT(*) as jumlah')
            ->whereIn('wilayah_id', $kabkotaProvinsi)
            ->where('tahun', $tahun)
            ->where('jenis_satuan', $jenisSatuan)
            ->where('status_satuan', $statusSatuan)
            ->whereIn('indikator_id', $indikatorIds)
            ->whereIn('label_capaian', array_keys($peringkatLabel))
            ->groupBy('indikator_id', 'label_capaian')
            ->get()
            ->groupBy('indikator_id');

        $labelWilayah = Capaian::query()
            ->where('wilayah_id', $wilayah->id)
            ->where('tahun', $tahun)
            ->where('jenis_satuan', $jenisSatuan)
            ->where('status_satuan', $statusSatuan)
            ->whereIn('indikator_id', $indikatorIds)
            ->pluck('label_capaian', 'indikator_id');

        $hasil = [];
        foreach ($indikatorIds as $id) {
            $labelSaya = $labelWilayah[$id] ?? null;
            $rankSaya = $peringkatLabel[$labelSaya] ?? null;
            if ($rankSaya === null) {
                $hasil[$id] = ['nilai' => 0.0, 'peringkat' => 0, 'dari' => 0];

                continue;
            }

            $baris = $distribusi->get($id, collect());
            $total = 0;
            $lebihBaik = 0;
            $samaAtauLebihBaik = 0;
            foreach ($baris as $b) {
                $total += $b->jumlah;
                $rankLain = $peringkatLabel[$b->label_capaian] ?? 0;
                if ($rankLain > $rankSaya) {
                    $lebihBaik += $b->jumlah;
                }
                if ($rankLain >= $rankSaya) {
                    $samaAtauLebihBaik += $b->jumlah;
                }
            }

            $nilai = $total > 1 ? $lebihBaik / ($total - 1) : 0.0;

            $hasil[$id] = [
                'nilai' => round($nilai, 4),
                'peringkat' => $lebihBaik + 1,
                'dari' => $total,
            ];
        }

        return $hasil;
    }

    /**
     * @param  list<int>  $indikatorIds
     * @param  array<string, int>  $peringkatLabel
     * @return array<int, array{nilai: float, peringkat: null, dari: null, pembanding_kab: string|null, pembanding_nama: string|null, pembanding_tersedia: bool}>
     */
    private function hitungPosisiTerhadapKabupaten(
        Wilayah $wilayah,
        int $tahun,
        string $jenisSatuan,
        string $statusSatuan,
        array $indikatorIds,
        array $peringkatLabel,
    ): array {
        $kabupaten = $wilayah->induk;
        $namaKabupaten = $kabupaten?->namaTampilan();

        $labelSekolah = Capaian::query()
            ->where('wilayah_id', $wilayah->id)
            ->where('tahun', $tahun)
            ->where('jenis_satuan', $jenisSatuan)
            ->where('status_satuan', $statusSatuan)
            ->whereIn('indikator_id', $indikatorIds)
            ->pluck('label_capaian', 'indikator_id');

        $labelKabupaten = $kabupaten
            ? Capaian::query()
                ->where('wilayah_id', $kabupaten->id)
                ->where('tahun', $tahun)
                ->where('jenis_satuan', $jenisSatuan)
                ->where('status_satuan', $statusSatuan)
                ->whereIn('indikator_id', $indikatorIds)
                ->pluck('label_capaian', 'indikator_id')
            : collect();

        $hasil = [];
        foreach ($indikatorIds as $id) {
            $rankSekolah = $peringkatLabel[$labelSekolah[$id] ?? ''] ?? null;
            $labelKabIni = $labelKabupaten[$id] ?? null;
            $rankKab = $peringkatLabel[$labelKabIni ?? ''] ?? null;

            $nilai = match (true) {
                $rankSekolah === null || $rankKab === null => 0.0,
                $rankSekolah < $rankKab => 1.0,
                $rankSekolah === $rankKab => 0.5,
                default => 0.0,
            };

            $hasil[$id] = [
                'nilai' => $nilai,
                'peringkat' => null,
                'dari' => null,
                'pembanding_kab' => $labelKabIni,
                'pembanding_nama' => $namaKabupaten,
                'pembanding_tersedia' => $rankKab !== null,
            ];
        }

        return $hasil;
    }

    /**
     * @param  list<array<string, mixed>>  $komponen
     * @param  array<string, mixed>|null  $posisi
     * @return list<array<string, mixed>>
     */
    private function tandaiPosisiSatuan(array $komponen, ?array $posisi): array
    {
        foreach ($komponen as $i => $k) {
            if (($k['kode'] ?? null) !== 'posisi') {
                continue;
            }
            $komponen[$i]['nama'] = 'Posisi relatif terhadap kabupaten';
            $komponen[$i]['pembanding'] = [
                'jenis' => 'kabupaten',
                'nama' => $posisi['pembanding_nama'] ?? null,
                'label' => $posisi['pembanding_kab'] ?? null,
                'tersedia' => (bool) ($posisi['pembanding_tersedia'] ?? false),
            ];
        }

        return $komponen;
    }

    /**
     * @param  list<int>  $indikatorIds
     * @param  list<string>  $labelBermasalah
     * @return array<int, array{nilai: float, bermasalah: int, total: int}>
     */
    private function hitungDampakTurunan(
        Wilayah $wilayah,
        int $tahun,
        string $jenisSatuan,
        string $statusSatuan,
        array $indikatorIds,
        array $labelBermasalah,
    ): array {
        $anak = Indikator::query()
            ->whereIn('induk_id', $indikatorIds)
            ->get(['id', 'induk_id']);

        if ($anak->isEmpty()) {
            return [];
        }

        $anakPerInduk = $anak->groupBy('induk_id');

        $labelAnak = Capaian::query()
            ->where('wilayah_id', $wilayah->id)
            ->where('tahun', $tahun)
            ->where('jenis_satuan', $jenisSatuan)
            ->where('status_satuan', $statusSatuan)
            ->whereIn('indikator_id', $anak->pluck('id'))
            ->pluck('label_capaian', 'indikator_id');

        $hasil = [];
        foreach ($anakPerInduk as $indukId => $daftarAnak) {
            $total = 0;
            $bermasalah = 0;
            foreach ($daftarAnak as $satu) {
                $label = $labelAnak[$satu->id] ?? null;
                if ($label === null) {
                    continue;
                }
                $total++;
                if (in_array($label, $labelBermasalah, true)) {
                    $bermasalah++;
                }
            }

            $hasil[$indukId] = [
                'nilai' => $total > 0 ? round($bermasalah / $total, 4) : 0.0,
                'bermasalah' => $bermasalah,
                'total' => $total,
            ];
        }

        return $hasil;
    }
}

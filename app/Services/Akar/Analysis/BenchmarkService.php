<?php

declare(strict_types=1);

namespace App\Services\Akar\Analysis;

use App\Models\Indikator;
use App\Models\Wilayah;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BenchmarkService
{
    private const NILAI_LABEL = ['Baik' => 3, 'Sedang' => 2, 'Kurang' => 1];

    private const TIDAK_TERSEDIA = 'Tidak Tersedia';

    /**
     * @return array{
     *   berlaku: bool,
     *   label_wilayah: string|null,
     *   perubahan_wilayah: string|null,
     *   peringkat: int|null,
     *   peringkat_hingga: int|null,
     *   dari: int,
     *   persentil: float|null,
     *   catatan: string|null
     * }
     */
    public function peringkat(
        Wilayah $wilayah,
        Indikator $indikator,
        int $tahun,
        string $jenisSatuan,
        string $statusSatuan,
    ): array {
        $indikatorId = $indikator->id;

        $kosong = [
            'berlaku' => true,
            'label_wilayah' => null,
            'perubahan_wilayah' => null,
            'peringkat' => null,
            'peringkat_hingga' => null,
            'dari' => 0,
            'persentil' => null,
            'catatan' => null,
        ];

        if ($wilayah->level === 'satuan') {
            return [
                ...$kosong,
                'berlaku' => false,
                'catatan' => 'Peringkat antarsekolah tidak tersedia; data sekolah lain tidak dipublikasikan.',
            ];
        }

        if ($wilayah->provinsi === null || $wilayah->provinsi === '') {
            return [...$kosong, 'catatan' => 'Wilayah tidak memiliki induk provinsi sehingga tidak dapat dibandingkan.'];
        }

        $baris = DB::table('capaian')
            ->where('wilayah_id', $wilayah->id)
            ->where('indikator_id', $indikatorId)
            ->where('tahun', $tahun)
            ->where('jenis_satuan', $jenisSatuan)
            ->where('status_satuan', $statusSatuan)
            ->first(['label_capaian', 'perubahan_nilai']);

        if ($baris === null || $baris->label_capaian === self::TIDAK_TERSEDIA) {
            return [
                ...$kosong,
                'dari' => $this->populasi($wilayah->provinsi, $indikatorId, $tahun, $jenisSatuan, $statusSatuan)->sum(),
                'catatan' => 'Wilayah ini tidak memiliki data untuk indikator ini, sehingga tidak masuk pemeringkatan.',
            ];
        }

        $jumlahPerLabel = $this->populasi($wilayah->provinsi, $indikatorId, $tahun, $jenisSatuan, $statusSatuan);
        $total = (int) $jumlahPerLabel->sum();

        $nilaiSaya = self::NILAI_LABEL[$baris->label_capaian] ?? 0;

        $lebihBaik = 0;
        foreach ($jumlahPerLabel as $label => $jumlah) {
            if ((self::NILAI_LABEL[$label] ?? 0) > $nilaiSaya) {
                $lebihBaik += (int) $jumlah;
            }
        }
        $sama = (int) ($jumlahPerLabel[$baris->label_capaian] ?? 0);

        $peringkat = $lebihBaik + 1;
        $peringkatHingga = $lebihBaik + $sama;

        $peringkatTengah = $lebihBaik + ($sama + 1) / 2;
        $persentil = $total > 1
            ? round(($total - $peringkatTengah) / ($total - 1), 4)
            : 1.0;

        return [
            'berlaku' => true,
            'label_wilayah' => $baris->label_capaian,
            'perubahan_wilayah' => $baris->perubahan_nilai,
            'peringkat' => $peringkat,
            'peringkat_hingga' => $peringkatHingga,
            'dari' => $total,
            'persentil' => $persentil,
            'catatan' => null,
        ];
    }

    /**
     * @return list<array{
     *   wilayah_id: int, nama: string, label_capaian: string,
     *   perubahan_nilai: string, peringkat: int
     * }>
     */
    public function tabelPeringkat(
        string $provinsi,
        Indikator $indikator,
        int $tahun,
        string $jenisSatuan,
        string $statusSatuan,
    ): array {
        $indikatorId = $indikator->id;

        $baris = DB::table('capaian as c')
            ->join('wilayah as w', 'w.id', '=', 'c.wilayah_id')
            ->where('w.level', 'kabkota')
            ->where('w.provinsi', $provinsi)
            ->where('c.indikator_id', $indikatorId)
            ->where('c.tahun', $tahun)
            ->where('c.jenis_satuan', $jenisSatuan)
            ->where('c.status_satuan', $statusSatuan)
            ->where('c.label_capaian', '!=', self::TIDAK_TERSEDIA)
            ->get(['w.id as wilayah_id', 'w.kabupaten_kota', 'c.label_capaian', 'c.perubahan_nilai']);

        $terurut = $baris->all();
        usort($terurut, function ($a, $b) {
            $selisih = (self::NILAI_LABEL[$b->label_capaian] ?? 0) <=> (self::NILAI_LABEL[$a->label_capaian] ?? 0);

            return $selisih !== 0
                ? $selisih
                : strcmp((string) $a->kabupaten_kota, (string) $b->kabupaten_kota);
        });

        return array_map(function ($r) use ($terurut) {
            $nilai = self::NILAI_LABEL[$r->label_capaian] ?? 0;
            $lebihBaik = count(array_filter(
                $terurut,
                fn ($lain) => (self::NILAI_LABEL[$lain->label_capaian] ?? 0) > $nilai
            ));

            return [
                'wilayah_id' => (int) $r->wilayah_id,
                'nama' => (string) $r->kabupaten_kota,
                'label_capaian' => $r->label_capaian,
                'perubahan_nilai' => $r->perubahan_nilai,
                'peringkat' => $lebihBaik + 1,
            ];
        }, $terurut);
    }

    /**
     * @return array{
     *   wilayah: array{nama: string, label: string|null, perubahan: string|null},
     *   kabupaten: array{nama: string|null, label: string|null, perubahan: string|null, tersedia: bool},
     *   provinsi: array{nama: string, label: string|null, perubahan: string|null, tersedia: bool},
     *   nasional: array{nama: string, label: string|null, perubahan: string|null, tersedia: bool}
     * }
     */
    public function pembanding(
        Wilayah $wilayah,
        Indikator $indikator,
        int $tahun,
        string $jenisSatuan,
        string $statusSatuan,
    ): array {
        $indikatorId = $indikator->id;

        $ambil = function (?int $wilayahId) use ($indikatorId, $tahun, $jenisSatuan, $statusSatuan) {
            if ($wilayahId === null) {
                return null;
            }

            return DB::table('capaian')
                ->where('wilayah_id', $wilayahId)
                ->where('indikator_id', $indikatorId)
                ->where('tahun', $tahun)
                ->where('jenis_satuan', $jenisSatuan)
                ->where('status_satuan', $statusSatuan)
                ->first(['label_capaian', 'perubahan_nilai']);
        };

        $kabupatenWilayah = $wilayah->level === 'satuan' && $wilayah->induk?->level === 'kabkota'
            ? $wilayah->induk
            : null;
        $provinsiWilayah = $wilayah->provinsi !== null
            ? Wilayah::query()->where('level', 'provinsi')->where('provinsi', $wilayah->provinsi)->first()
            : null;
        $nasionalWilayah = Wilayah::query()->where('level', 'nasional')->first();

        $sendiri = $ambil($wilayah->id);
        $kabupaten = $ambil($kabupatenWilayah?->id);
        $provinsi = $ambil($provinsiWilayah?->id);
        $nasional = $ambil($nasionalWilayah?->id);

        return [
            'wilayah' => [
                'nama' => $wilayah->namaTampilan(),
                'label' => $sendiri->label_capaian ?? null,
                'perubahan' => $sendiri->perubahan_nilai ?? null,
            ],
            'kabupaten' => [
                'nama' => $kabupatenWilayah?->namaTampilan(),
                'label' => $kabupaten->label_capaian ?? null,
                'perubahan' => $kabupaten->perubahan_nilai ?? null,
                'tersedia' => $kabupaten !== null,
            ],
            'provinsi' => [
                'nama' => $provinsiWilayah?->namaTampilan() ?? ('Provinsi '.($wilayah->provinsi ?? '')),
                'label' => $provinsi->label_capaian ?? null,
                'perubahan' => $provinsi->perubahan_nilai ?? null,
                'tersedia' => $provinsi !== null,
            ],
            'nasional' => [
                'nama' => 'Nasional',
                'label' => $nasional->label_capaian ?? null,
                'perubahan' => $nasional->perubahan_nilai ?? null,
                'tersedia' => $nasional !== null,
            ],
        ];
    }

    /** @return Collection<string, int> */
    private function populasi(
        string $provinsi,
        int $indikatorId,
        int $tahun,
        string $jenisSatuan,
        string $statusSatuan,
    ) {
        return DB::table('capaian as c')
            ->join('wilayah as w', 'w.id', '=', 'c.wilayah_id')
            ->where('w.level', 'kabkota')
            ->where('w.provinsi', $provinsi)
            ->where('c.indikator_id', $indikatorId)
            ->where('c.tahun', $tahun)
            ->where('c.jenis_satuan', $jenisSatuan)
            ->where('c.status_satuan', $statusSatuan)
            ->where('c.label_capaian', '!=', self::TIDAK_TERSEDIA)
            ->groupBy('c.label_capaian')
            ->selectRaw('c.label_capaian as label, count(*) as jumlah')
            ->pluck('jumlah', 'label')
            ->map(fn ($n) => (int) $n);
    }
}

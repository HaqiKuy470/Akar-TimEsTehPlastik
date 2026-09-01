<?php

declare(strict_types=1);

namespace App\Services\Akar\Analysis;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Support\Collection;

class ProfilCapaianService
{
    /**
     * @return array{
     *   wilayah: array{nama: string, level: string},
     *   tahun: int,
     *   jenis_satuan: string,
     *   status_satuan: string,
     *   tersedia: bool,
     *   ringkasan: array{merah: int, kuning: int, hijau: int, tidak_tersedia: int, total: int},
     *   dimensi: array<string, array{kode: string, nama: string, indikator: list<array<string, mixed>>, hitung: array<string, int>}>,
     *   dimensi_grafik: list<array{kode: string, nama: string, hitung: array<string, int>}>,
     *   tidak_tersedia: list<array<string, mixed>>
     * }
     */
    public function untukWilayah(Wilayah $wilayah, int $tahun, string $jenisSatuan, string $statusSatuan): array
    {
        $rangka = [
            'wilayah' => ['nama' => $wilayah->namaTampilan(), 'level' => $wilayah->level],
            'tahun' => $tahun,
            'jenis_satuan' => $jenisSatuan,
            'status_satuan' => $statusSatuan,
            'tersedia' => false,
            'ringkasan' => ['merah' => 0, 'kuning' => 0, 'hijau' => 0, 'tidak_tersedia' => 0, 'total' => 0],
            'dimensi' => [],
            'dimensi_grafik' => [],
            'tidak_tersedia' => [],
        ];

        $satuan = $wilayah->level === 'satuan';

        $impor = $satuan
            ? $this->imporSatuan($wilayah, $tahun)
            : $this->imporDaerah($tahun);
        if ($impor === null) {
            return $rangka;
        }

        $jenisLayanan = PemetaanJenisLayanan::dari($jenisSatuan);
        $kolomKetersediaan = $satuan ? 'tersedia_satuan' : 'tersedia_kabkota';
        $universe = $this->indikatorRelevan($impor->id, $jenisSatuan, $jenisLayanan, $kolomKetersediaan);
        if ($universe->isEmpty()) {
            return $rangka;
        }

        $capaian = Capaian::query()
            ->where('wilayah_id', $wilayah->id)
            ->where('tahun', $tahun)
            ->where('jenis_satuan', $jenisSatuan)
            ->where('status_satuan', $statusSatuan)
            ->get(['indikator_id', 'label_capaian', 'perubahan_nilai'])
            ->keyBy('indikator_id');

        $namaDimensi = (array) config('akar.dimensi', []);
        $labelMerah = (array) config('akar.label_merah', ['Kurang']);
        $labelKuning = (array) config('akar.label_kuning', ['Sedang']);
        $labelHijau = (array) config('akar.label_hijau', ['Baik']);

        $dimensi = [];
        $tidakTersedia = [];
        $ringkasan = $rangka['ringkasan'];

        foreach ($universe as $indikator) {
            $baris = $capaian->get($indikator->id);
            $label = $baris->label_capaian ?? 'Tidak Tersedia';

            $status = match (true) {
                in_array($label, $labelMerah, true) => 'merah',
                in_array($label, $labelKuning, true) => 'kuning',
                in_array($label, $labelHijau, true) => 'hijau',
                default => 'kosong',
            };

            $entri = [
                'nomor' => $indikator->nomor,
                'nama' => $indikator->nama,
                'induk_nomor' => $indikator->induk?->nomor,
                'label_capaian' => $label,
                'perubahan_nilai' => $baris->perubahan_nilai ?? 'Tidak Tersedia',
                'status' => $status,
                'ambang' => [
                    'merah' => $indikator->definisi_merah,
                    'kuning' => $indikator->definisi_kuning,
                    'hijau' => $indikator->definisi_hijau,
                ],
            ];

            $ringkasan['total']++;

            $kode = $indikator->dimensi;
            $dimensi[$kode] ??= [
                'kode' => $kode,
                'nama' => $namaDimensi[$kode] ?? $kode,
                'indikator' => [],
                'hitung' => ['merah' => 0, 'kuning' => 0, 'hijau' => 0, 'kosong' => 0],
            ];
            $dimensi[$kode]['hitung'][$status]++;

            if ($status === 'kosong') {
                $ringkasan['tidak_tersedia']++;
                $tidakTersedia[] = $entri;

                continue;
            }

            $ringkasan[$status]++;
            $dimensi[$kode]['indikator'][] = $entri;
        }

        $dimensiGrafik = array_values(array_filter(
            $dimensi,
            static fn ($d) => array_sum($d['hitung']) > 0,
        ));
        usort($dimensiGrafik, static fn ($a, $b) => strcmp($a['kode'], $b['kode']));

        $dimensi = array_filter($dimensi, static fn ($d) => $d['indikator'] !== []);
        ksort($dimensi);

        return [
            ...$rangka,
            'tersedia' => true,
            'ringkasan' => $ringkasan,
            'dimensi' => $dimensi,
            'dimensi_grafik' => $dimensiGrafik,
            'tidak_tersedia' => $tidakTersedia,
        ];
    }

    private function imporDaerah(int $tahun): ?ImporBerkas
    {
        return ImporBerkas::query()
            ->where('jenis', 'daerah')
            ->where('tahun_edisi', $tahun)
            ->where('status', 'selesai')
            ->latest('id')
            ->first();
    }

    private function imporSatuan(Wilayah $wilayah, int $tahun): ?ImporBerkas
    {
        $imporIds = Capaian::query()
            ->where('wilayah_id', $wilayah->id)
            ->where('tahun', $tahun)
            ->distinct()
            ->pluck('impor_id');

        if ($imporIds->isEmpty()) {
            return null;
        }

        return ImporBerkas::query()
            ->whereIn('id', $imporIds)
            ->where('jenis', 'satuan')
            ->where('status', 'selesai')
            ->latest('id')
            ->first();
    }

    /**
     * @param  string  $kolomKetersediaan  'tersedia_kabkota' (daerah) / 'tersedia_satuan' (sekolah)
     * @return Collection<int, Indikator>
     */
    private function indikatorRelevan(int $imporId, string $jenisSatuan, string $jenisLayanan, string $kolomKetersediaan): Collection
    {
        $idRelevan = Capaian::query()
            ->where('impor_id', $imporId)
            ->where('jenis_satuan', $jenisSatuan)
            ->distinct()
            ->pluck('indikator_id');

        if ($idRelevan->isEmpty()) {
            return collect();
        }

        return Indikator::query()
            ->with('induk:id,nomor')
            ->whereIn('id', $idRelevan)
            ->where('jenis_layanan', $jenisLayanan)
            ->where($kolomKetersediaan, true)
            ->get()
            ->sortBy(fn (Indikator $i) => $this->kunciUrut($i->nomor), SORT_NATURAL)
            ->values();
    }

    private function kunciUrut(string $nomor): string
    {
        return preg_replace_callback('/\d+/', static fn ($m) => str_pad($m[0], 3, '0', STR_PAD_LEFT), $nomor) ?? $nomor;
    }
}

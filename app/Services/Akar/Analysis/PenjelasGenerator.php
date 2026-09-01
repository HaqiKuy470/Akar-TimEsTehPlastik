<?php

declare(strict_types=1);

namespace App\Services\Akar\Analysis;

/**
 * Merangkai kalimat penjelas Indonesia biasa untuk satu indikator prioritas,
 * dari fakta analisis (bukan angka skor mentah). Tidak pernah kosong: minimal
 * kondisi label dan arah perubahan selalu dijelaskan.
 */
class PenjelasGenerator
{
    /**
     * @param  list<array{kode: string, nama: string, bobot_maks: int|float, nilai_0_1: float, kontribusi: float}>  $komponenSkor
     * @param  array{
     *   label?: string,
     *   perubahan?: string,
     *   peringkat?: int|null,
     *   dari?: int|null,
     *   pembanding_kabupaten?: array{nama?: string, label?: string, tersedia?: bool}|null,
     *   anak_bermasalah?: int|null,
     *   anak_total?: int|null
     * }  $konteks
     */
    public function untuk(array $komponenSkor, array $konteks): string
    {
        $kalimat = [];

        $kalimat[] = $this->kalimatKondisi(
            $konteks['label'] ?? null,
            $konteks['perubahan'] ?? null,
        );

        $peringkat = $this->kalimatPeringkat($konteks['peringkat'] ?? null, $konteks['dari'] ?? null);
        if ($peringkat !== null) {
            $kalimat[] = $peringkat;
        }

        // Mode satuan: pembandingnya agregat kabupaten induk, bukan peringkat.
        $kabupaten = $this->kalimatKabupaten($konteks['pembanding_kabupaten'] ?? null, $konteks['label'] ?? null);
        if ($kabupaten !== null) {
            $kalimat[] = $kabupaten;
        }

        $turunan = $this->kalimatTurunan(
            $konteks['anak_bermasalah'] ?? null,
            $konteks['anak_total'] ?? null,
        );
        if ($turunan !== null) {
            $kalimat[] = $turunan;
        }

        return implode(' ', array_filter($kalimat));
    }

    private function kalimatKondisi(?string $label, ?string $perubahan): string
    {
        $labelFrasa = match ($label) {
            'Kurang' => 'Berlabel Kurang',
            'Sedang' => 'Berlabel Sedang',
            'Baik' => 'Berlabel Baik',
            default => 'Kondisi indikator ini perlu diperhatikan',
        };

        $perubahanFrasa = match ($perubahan) {
            'Turun' => 'dan menurun dibanding tahun lalu',
            'Naik' => 'namun membaik dibanding tahun lalu',
            'Tidak berubah' => 'dan belum bergerak dibanding tahun lalu',
            default => null,
        };

        return $perubahanFrasa !== null
            ? "{$labelFrasa} {$perubahanFrasa}."
            : "{$labelFrasa}.";
    }

    private function kalimatPeringkat(?int $peringkat, ?int $dari): ?string
    {
        if ($peringkat === null || $dari === null || $dari < 2) {
            return null;
        }

        return "Berada di peringkat {$peringkat} dari {$dari} kabupaten/kota di provinsi yang sama.";
    }

    /**
     * @param  array{nama?: string, label?: string, tersedia?: bool}|null  $kabupaten
     */
    private function kalimatKabupaten(?array $kabupaten, ?string $labelSekolah): ?string
    {
        if ($kabupaten === null || ($kabupaten['tersedia'] ?? false) !== true) {
            return null;
        }

        $nama = $kabupaten['nama'] ?? 'kabupaten';
        $labelKab = $kabupaten['label'] ?? null;

        $peringkatMutu = ['Kurang' => 1, 'Sedang' => 2, 'Baik' => 3];
        $s = $peringkatMutu[$labelSekolah] ?? null;
        $k = $peringkatMutu[$labelKab] ?? null;

        $banding = match (true) {
            $s !== null && $k !== null && $s < $k => "lebih rendah daripada rata-rata {$nama}",
            $s !== null && $k !== null && $s > $k => "lebih baik daripada rata-rata {$nama}",
            $s !== null && $k !== null => "setara dengan rata-rata {$nama}",
            default => "dapat dibandingkan dengan rata-rata {$nama} yang berlabel {$labelKab}",
        };

        return "Capaian sekolah {$banding}.";
    }

    private function kalimatTurunan(?int $bermasalah, ?int $total): ?string
    {
        if ($bermasalah === null || $total === null || $total < 1 || $bermasalah < 1) {
            return null;
        }

        return "Memengaruhi {$bermasalah} dari {$total} indikator turunan yang juga bermasalah.";
    }
}

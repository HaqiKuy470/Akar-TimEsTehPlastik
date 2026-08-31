<?php

declare(strict_types=1);

namespace App\Services\Akar\Analysis;

/**
 * Menyusun kalimat penjelas untuk satu indikator prioritas.
 *
 * Kalimatnya harus bisa dibaca kepala bidang di Dinas Pendidikan tanpa latar
 * belakang teknis, dan dibawa langsung ke forum perencanaan. Karena itu kelas
 * ini merangkai kalimat Indonesia biasa dari fakta-fakta analisis, bukan
 * membeberkan angka skor mentah.
 *
 * Kalimat penjelas tidak boleh kosong. Bila konteks minim, minimal kondisi
 * label dan arah perubahan selalu dijelaskan.
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

    private function kalimatTurunan(?int $bermasalah, ?int $total): ?string
    {
        if ($bermasalah === null || $total === null || $total < 1 || $bermasalah < 1) {
            return null;
        }

        return "Memengaruhi {$bermasalah} dari {$total} indikator turunan yang juga bermasalah.";
    }
}

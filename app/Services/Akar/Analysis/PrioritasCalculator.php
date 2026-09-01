<?php

declare(strict_types=1);

namespace App\Services\Akar\Analysis;

use InvalidArgumentException;

class PrioritasCalculator
{
    private array $bobotKomponen;

    private array $nilaiLabel;

    private array $nilaiPerubahan;

    /** @param  array<string, mixed>|null  $config  isi config/akar.php; null = ambil dari container */
    public function __construct(?array $config = null)
    {
        $config ??= (array) config('akar');

        $this->bobotKomponen = $config['bobot_komponen'];
        $this->nilaiLabel = $config['nilai_label'];
        $this->nilaiPerubahan = $config['nilai_perubahan'];
    }

    /**
     * @param  array{
     *   label: string,
     *   perubahan: string,
     *   bobot_posisi?: float,
     *   bobot_turunan?: float
     * }  $masukan
     * @return array{
     *   skor: float,
     *   komponen: list<array{kode: string, nama: string, bobot_maks: int|float, nilai_0_1: float, kontribusi: float}>
     * }
     */
    public function hitung(array $masukan): array
    {
        $label = $masukan['label'] ?? '';
        $perubahan = $masukan['perubahan'] ?? '';

        if (! array_key_exists($label, $this->nilaiLabel)) {
            throw new InvalidArgumentException(
                "Label capaian '{$label}' tidak dapat diberi skor prioritas. ".
                'Indikator "Tidak Tersedia" harus disaring lebih dulu oleh pemanggil.'
            );
        }

        $nilaiPosisi = $this->batasiSatuan($masukan['bobot_posisi'] ?? 0.0);
        $nilaiTurunan = $this->batasiSatuan($masukan['bobot_turunan'] ?? 0.0);

        $komponen = [
            $this->komponen('label', 'Label capaian', $this->nilaiLabel[$label]),
            $this->komponen('perubahan', 'Arah perubahan', $this->nilaiPerubahan[$perubahan] ?? 0.0),
            $this->komponen('posisi', 'Posisi relatif terhadap daerah lain', $nilaiPosisi),
            $this->komponen('turunan', 'Dampak ke indikator turunan', $nilaiTurunan),
        ];

        $skor = array_sum(array_column($komponen, 'kontribusi'));

        return [
            'skor' => round($skor, 2),
            'komponen' => $komponen,
        ];
    }

    /** @return array{kode: string, nama: string, bobot_maks: int|float, nilai_0_1: float, kontribusi: float} */
    private function komponen(string $kode, string $nama, float $nilai): array
    {
        $bobot = $this->bobotKomponen[$kode] ?? 0;
        $nilai = $this->batasiSatuan($nilai);

        return [
            'kode' => $kode,
            'nama' => $nama,
            'bobot_maks' => $bobot,
            'nilai_0_1' => round($nilai, 4),
            'kontribusi' => round($bobot * $nilai, 2),
        ];
    }

    private function batasiSatuan(float $nilai): float
    {
        return max(0.0, min(1.0, $nilai));
    }
}

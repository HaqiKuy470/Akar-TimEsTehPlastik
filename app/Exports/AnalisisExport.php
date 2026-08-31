<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Analisis;
use App\Models\AnalisisPrioritas;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Data mentah hasil analisis: satu baris per indikator prioritas, untuk diolah
 * lebih lanjut di luar aplikasi. Kolom komponen skor dipisah agar skor tetap
 * dapat ditelusuri di lembar kerja.
 */
class AnalisisExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  array<int, string>  $labelCapaian  indikator_id => label capaian
     */
    public function __construct(
        private readonly Analisis $analisis,
        private readonly array $labelCapaian,
    ) {}

    public function title(): string
    {
        return 'Prioritas';
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Peringkat',
            'Nomor indikator',
            'Nama indikator',
            'Dimensi',
            'Label capaian',
            'Skor prioritas',
            'Skor label capaian',
            'Skor arah perubahan',
            'Skor posisi relatif',
            'Skor dampak turunan',
            'Akar masalah terkuat',
            'Tingkat keyakinan',
        ];
    }

    /**
     * @return list<list<string|int|float|null>>
     */
    public function array(): array
    {
        return $this->analisis->prioritas
            ->sortBy('peringkat')
            ->map(function (AnalisisPrioritas $p) {
                $komponen = $this->komponen($p->komponen_skor ?? []);
                $akar = $p->akar
                    ->reject(fn ($a) => $a->keyakinan->value === 'tidak_cukup_bukti')
                    ->sortBy(fn ($a) => $this->urutanKeyakinan($a->keyakinan->value))
                    ->first();

                return [
                    $p->peringkat,
                    $p->indikator?->nomor ?? '',
                    $p->indikator?->nama ?? '',
                    $p->indikator?->dimensi ?? '',
                    $this->labelCapaian[$p->indikator_id] ?? 'Tidak Tersedia',
                    (float) $p->skor,
                    $komponen['label'] ?? null,
                    $komponen['perubahan'] ?? null,
                    $komponen['posisi'] ?? null,
                    $komponen['turunan'] ?? null,
                    $akar?->label ?? '',
                    $akar?->keyakinan->label() ?? '',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Kontribusi tiap komponen skor menurut kodenya.
     *
     * @param  array<mixed>  $komponenSkor
     * @return array<string, float>
     */
    private function komponen(array $komponenSkor): array
    {
        $hasil = [];

        foreach ($komponenSkor as $kunci => $nilai) {
            if (! is_array($nilai)) {
                continue;
            }
            $kode = (string) ($nilai['kode'] ?? $kunci);
            $hasil[$kode] = (float) ($nilai['kontribusi'] ?? 0);
        }

        return $hasil;
    }

    private function urutanKeyakinan(string $keyakinan): int
    {
        return match ($keyakinan) {
            'kuat' => 0,
            'sedang' => 1,
            'lemah' => 2,
            default => 3,
        };
    }
}

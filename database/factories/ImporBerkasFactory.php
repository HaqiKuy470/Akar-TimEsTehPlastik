<?php

namespace Database\Factories;

use App\Models\ImporBerkas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImporBerkas>
 */
class ImporBerkasFactory extends Factory
{
    protected $model = ImporBerkas::class;

    public function definition(): array
    {
        return [
            'nama_berkas' => fake()->word().'_rapor_pendidikan.xlsx',
            'jenis' => 'daerah',
            'tahun_edisi' => fake()->numberBetween(2022, 2025),
            'hash_berkas' => hash('sha256', fake()->unique()->uuid()),
            'status' => 'selesai',
            'jumlah_baris' => fake()->numberBetween(100, 1000),
            'catatan_galat' => null,
            'diunggah_oleh' => null,
            'diproses_pada' => now(),
        ];
    }

    public function metadata(): static
    {
        return $this->state(fn () => ['jenis' => 'metadata', 'tahun_edisi' => null]);
    }
}

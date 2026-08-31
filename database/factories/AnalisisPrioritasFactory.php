<?php

namespace Database\Factories;

use App\Models\Analisis;
use App\Models\AnalisisPrioritas;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<AnalisisPrioritas>
 */
class AnalisisPrioritasFactory extends Factory
{
    protected $model = AnalisisPrioritas::class;

    public function definition(): array
    {
        $dimensi = fake()->randomElement(['A', 'B', 'C', 'D', 'E']);
        $nomor = $dimensi.'.'.fake()->unique()->numberBetween(1, 999);

        return [
            'analisis_id' => Analisis::factory(),
            // Model Indikator dimiliki komponen impor; sisipkan baris minimal.
            'indikator_id' => fn () => DB::table('indikator')->insertGetId([
                'nomor' => $nomor,
                'induk_id' => null,
                'dimensi' => $dimensi,
                'nama' => 'Indikator '.$nomor,
                'jenis_layanan' => 'Pendidikan Dasar dan Pendidikan Menengah',
                'tersedia_satuan' => true,
                'tersedia_kabkota' => true,
                'tersedia_provinsi' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            'skor' => fake()->randomFloat(2, 0, 100),
            'komponen_skor' => [
                'label' => ['nilai' => 1.0, 'bobot' => 40, 'kontribusi' => 40],
                'perubahan' => ['nilai' => 0.5, 'bobot' => 25, 'kontribusi' => 12.5],
                'posisi' => ['nilai' => 0.7, 'bobot' => 20, 'kontribusi' => 14],
                'turunan' => ['nilai' => 0.5, 'bobot' => 15, 'kontribusi' => 7.5],
            ],
            'kalimat_penjelas' => 'Berlabel Kurang dan menurun dibanding tahun lalu.',
            'peringkat' => fake()->numberBetween(1, 38),
        ];
    }
}

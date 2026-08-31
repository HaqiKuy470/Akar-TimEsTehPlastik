<?php

namespace Database\Factories;

use App\Models\Analisis;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<Analisis>
 */
class AnalisisFactory extends Factory
{
    protected $model = Analisis::class;

    public function definition(): array
    {
        return [
            // Model Wilayah dimiliki komponen impor; di sini cukup sisipkan
            // baris wilayah minimal agar foreign key terpenuhi.
            'wilayah_id' => fn () => DB::table('wilayah')->insertGetId([
                'level' => 'kabkota',
                'provinsi' => fake()->state(),
                'kabupaten_kota' => 'Kab. '.fake()->unique()->city(),
                'nama_satuan' => null,
                'induk_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            'tahun' => fake()->numberBetween(2022, 2025),
            'jenis_satuan' => fake()->randomElement(['SD Umum', 'SMP Umum', 'SMA Umum', 'SMK Umum']),
            'status_satuan' => fake()->randomElement(['Negeri', 'Swasta', 'Semua (Negeri dan Swasta)']),
            'bobot_dipakai' => config('akar'),
            'dibuat_oleh' => User::factory(),
        ];
    }
}

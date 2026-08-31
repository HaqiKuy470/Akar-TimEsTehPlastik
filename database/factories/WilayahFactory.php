<?php

namespace Database\Factories;

use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wilayah>
 */
class WilayahFactory extends Factory
{
    protected $model = Wilayah::class;

    public function definition(): array
    {
        return [
            'level' => 'kabkota',
            'provinsi' => fake()->randomElement(['Jawa Timur', 'Jawa Barat', 'Bali', 'Aceh']),
            'kabupaten_kota' => 'Kabupaten '.fake()->unique()->city(),
            'nama_satuan' => null,
            'induk_id' => null,
        ];
    }

    public function provinsi(): static
    {
        return $this->state(fn () => [
            'level' => 'provinsi',
            'kabupaten_kota' => null,
            'nama_satuan' => null,
        ]);
    }

    public function nasional(): static
    {
        return $this->state(fn () => [
            'level' => 'nasional',
            'provinsi' => null,
            'kabupaten_kota' => null,
            'nama_satuan' => null,
        ]);
    }
}

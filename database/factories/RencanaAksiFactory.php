<?php

namespace Database\Factories;

use App\Models\Analisis;
use App\Models\RencanaAksi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RencanaAksi>
 */
class RencanaAksiFactory extends Factory
{
    protected $model = RencanaAksi::class;

    public function definition(): array
    {
        return [
            'analisis_id' => Analisis::factory(),
            'judul' => 'Rencana Tindak Lanjut '.fake()->year(),
            'dibuat_oleh' => User::factory(),
        ];
    }
}

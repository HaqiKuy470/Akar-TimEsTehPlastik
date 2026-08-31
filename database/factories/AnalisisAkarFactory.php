<?php

namespace Database\Factories;

use App\Enums\Keyakinan;
use App\Models\AnalisisAkar;
use App\Models\AnalisisPrioritas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalisisAkar>
 */
class AnalisisAkarFactory extends Factory
{
    protected $model = AnalisisAkar::class;

    public function definition(): array
    {
        return [
            'analisis_prioritas_id' => AnalisisPrioritas::factory(),
            'kode_akar' => fake()->randomElement(['pembelajaran', 'iklim', 'sarana', 'kompetensi_guru']),
            'label' => fake()->randomElement([
                'Kualitas praktik pembelajaran belum optimal',
                'Iklim satuan pendidikan menghambat pembelajaran',
                'Budaya refleksi dan perbaikan pembelajaran belum berjalan',
            ]),
            'bukti' => [
                ['nomor' => 'D.1', 'nama' => 'Kualitas pembelajaran', 'label' => 'Sedang'],
                ['nomor' => 'D.2', 'nama' => 'Refleksi dan perbaikan', 'label' => 'Kurang'],
            ],
            'keyakinan' => fake()->randomElement(Keyakinan::cases()),
        ];
    }

    public function keyakinan(Keyakinan $keyakinan): static
    {
        return $this->state(fn () => ['keyakinan' => $keyakinan]);
    }
}

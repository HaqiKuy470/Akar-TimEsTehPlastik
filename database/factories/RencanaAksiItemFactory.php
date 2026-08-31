<?php

namespace Database\Factories;

use App\Models\RencanaAksi;
use App\Models\RencanaAksiItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RencanaAksiItem>
 */
class RencanaAksiItemFactory extends Factory
{
    protected $model = RencanaAksiItem::class;

    public function definition(): array
    {
        return [
            'rencana_aksi_id' => RencanaAksi::factory(),
            'masalah' => 'Kemampuan literasi SMP berlabel Kurang',
            'akar_masalah' => 'Budaya refleksi dan perbaikan pembelajaran lemah',
            'kegiatan' => 'Pendampingan komunitas belajar antarsekolah pada 12 sekolah prioritas.',
            'penanggung_jawab' => 'Bidang Pembinaan SMP',
            'indikator_keberhasilan' => 'D.2 naik dari Kurang ke Sedang pada Asesmen Nasional berikutnya',
            'perkiraan_waktu' => fake()->randomElement(['Triwulan I', 'Triwulan I–II', 'Triwulan II–III']),
            'urutan' => fake()->numberBetween(0, 10),
        ];
    }
}

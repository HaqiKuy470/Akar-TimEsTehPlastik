<?php

namespace Database\Factories;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Capaian>
 */
class CapaianFactory extends Factory
{
    protected $model = Capaian::class;

    public function definition(): array
    {
        return [
            'impor_id' => ImporBerkas::factory(),
            'wilayah_id' => Wilayah::factory(),
            // Model Indikator diisi dari berkas Metadata, bukan lewat factory,
            // jadi di sini indikator minimal dibuat langsung agar foreign key
            // terpenuhi tanpa bergantung pada IndikatorFactory.
            'indikator_id' => fn () => Indikator::query()->firstOrCreate(
                ['nomor' => 'A.1', 'jenis_layanan' => 'Pendidikan Dasar dan Pendidikan Menengah', 'nama' => 'Kemampuan literasi'],
                ['dimensi' => 'A', 'tersedia_kabkota' => true, 'tersedia_provinsi' => true],
            )->id,
            'tahun' => 2025,
            'jenis_satuan' => fake()->randomElement(['SD Umum', 'SMP Umum', 'SMA Umum', 'SMK Umum']),
            'status_satuan' => 'Semua (Negeri dan Swasta)',
            'label_capaian' => fake()->randomElement(['Baik', 'Sedang', 'Kurang']),
            'perubahan_nilai' => fake()->randomElement(['Naik', 'Turun', 'Tidak berubah']),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Indikator;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Indikator>
 *
 * Di produksi tabel indikator selalu diisi dari berkas Metadata resmi
 * (MetadataIndikatorParser). Factory ini hanya untuk pengujian.
 */
class IndikatorFactory extends Factory
{
    protected $model = Indikator::class;

    public function definition(): array
    {
        $dimensi = fake()->randomElement(['A', 'B', 'C', 'D', 'E']);
        $nomor = $dimensi.'.'.fake()->unique()->numberBetween(1, 400);

        return [
            'nomor' => $nomor,
            'induk_id' => null,
            'dimensi' => $dimensi,
            'nama' => fake()->sentence(3),
            'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH,
            'definisi_konseptual' => fake()->sentence(),
            'definisi_operasional' => fake()->sentence(),
            'sumber_data' => 'Asesmen Nasional',
            'label_merah' => 'Kurang',
            'definisi_merah' => 'Kurang dari 40% peserta didik mencapai kompetensi minimum.',
            'label_kuning' => 'Sedang',
            'definisi_kuning' => '40% - 70% peserta didik mencapai kompetensi minimum.',
            'label_hijau' => 'Baik',
            'definisi_hijau' => 'Lebih dari 70% peserta didik mencapai kompetensi minimum.',
            'tersedia_satuan' => true,
            'tersedia_kabkota' => true,
            'tersedia_provinsi' => true,
        ];
    }

    public function nomor(string $nomor): static
    {
        return $this->state(fn () => [
            'nomor' => $nomor,
            'dimensi' => strtoupper(substr($nomor, 0, 1)),
        ]);
    }
}

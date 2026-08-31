<?php

namespace Tests\Feature;

use App\Models\Indikator;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_membuat_peran_dan_akun_demo(): void
    {
        $this->seed(DemoSeeder::class);

        $this->assertSame(
            ['admin', 'analis_dinas', 'kepala_sekolah'],
            Role::query()->orderBy('name')->pluck('name')->all(),
        );

        foreach (['admin@akar.test', 'analis@akar.test', 'kepala@akar.test'] as $email) {
            $this->assertDatabaseHas('users', ['email' => $email]);
        }

        $admin = User::where('email', 'admin@akar.test')->first();
        $this->assertTrue($admin->hasRole('admin'));
    }

    public function test_mengimpor_indikator_bila_berkas_metadata_tersedia(): void
    {
        $metadata = base_path('dataset/dataset-pendidikan/METADATA_INDIKATOR_RAPOR_PENDIDIKAN.csv');

        if (! is_file($metadata)) {
            $this->markTestSkipped('Berkas Metadata tidak tersedia di lingkungan ini.');
        }

        $this->seed(DemoSeeder::class);

        $this->assertSame(274, Indikator::count());
    }

    public function test_tidak_gagal_tanpa_berkas_dataset(): void
    {
        // Seeder harus tetap sukses meski folder dataset tidak ada:
        // langkah impor berkas dilewati dengan peringatan, bukan pengecualian.
        $this->seed(DemoSeeder::class);

        $this->assertGreaterThanOrEqual(3, User::count());
    }
}

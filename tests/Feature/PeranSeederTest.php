<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AkunDemoSeeder;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PeranSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_membuat_empat_peran(): void
    {
        $this->seed(PeranSeeder::class);

        $this->assertEqualsCanonicalizing(
            ['superadmin', 'admin', 'analis_dinas', 'kepala_sekolah'],
            Role::pluck('name')->all(),
        );
    }

    public function test_membuat_seluruh_izin(): void
    {
        $this->seed(PeranSeeder::class);

        $this->assertEqualsCanonicalizing(
            array_keys(PeranSeeder::IZIN),
            Permission::pluck('name')->all(),
        );
    }

    public function test_izin_termap_ke_peran_sesuai_prd(): void
    {
        $this->seed(PeranSeeder::class);

        $admin = Role::findByName('admin');
        $analis = Role::findByName('analis_dinas');
        $kepala = Role::findByName('kepala_sekolah');

        $this->assertTrue($admin->hasPermissionTo('impor.daerah'));
        $this->assertFalse($analis->hasPermissionTo('impor.daerah'));
        $this->assertFalse($kepala->hasPermissionTo('impor.daerah'));

        $this->assertTrue($analis->hasPermissionTo('analisis.jalankan'));
        $this->assertTrue($analis->hasPermissionTo('rencana.kelola'));

        $this->assertTrue($kepala->hasPermissionTo('berkas.satuan.unggah'));
        $this->assertTrue($kepala->hasPermissionTo('analisis.jalankan'));
        $this->assertFalse($analis->hasPermissionTo('berkas.satuan.unggah'));

        // superadmin HANYA mengelola akun; tak boleh menjalankan analisis.
        $super = Role::findByName('superadmin');
        $this->assertTrue($super->hasPermissionTo('akun.kelola'));
        $this->assertFalse($super->hasPermissionTo('analisis.jalankan'));
        $this->assertFalse($analis->hasPermissionTo('akun.kelola'));
    }

    public function test_seeder_idempoten(): void
    {
        $this->seed(PeranSeeder::class);
        $this->seed(PeranSeeder::class);

        $this->assertSame(4, Role::count());
        $this->assertSame(count(PeranSeeder::IZIN), Permission::count());
    }

    public function test_akun_demo_dibuat_dengan_peran(): void
    {
        $this->seed(PeranSeeder::class);
        $this->seed(AkunDemoSeeder::class);

        $this->assertSame(4, User::count());

        $admin = User::where('email', 'admin@akar.test')->first();
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->can('impor.daerah'));

        $kepala = User::where('email', 'kepala@akar.test')->first();
        $this->assertTrue($kepala->hasRole('kepala_sekolah'));
        $this->assertTrue($kepala->can('berkas.satuan.unggah'));
    }

    public function test_akun_demo_idempoten(): void
    {
        $this->seed(PeranSeeder::class);
        $this->seed(AkunDemoSeeder::class);
        $this->seed(AkunDemoSeeder::class);

        $this->assertSame(4, User::count());
    }
}

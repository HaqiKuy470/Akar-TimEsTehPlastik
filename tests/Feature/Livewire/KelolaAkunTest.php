<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Superadmin\KelolaAkun;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class KelolaAkunTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);
    }

    private function superadmin(): User
    {
        $u = User::factory()->create();
        $u->assignRole('superadmin');

        return $u;
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('akun'))->assertRedirect(route('login'));
    }

    public function test_superadmin_dapat_membuka_halaman(): void
    {
        $this->withoutVite();

        $this->actingAs($this->superadmin())
            ->get(route('akun'))
            ->assertOk()
            ->assertSee('Kelola akun')
            ->assertSee('Buat akun baru');
    }

    public function test_selain_superadmin_tidak_dapat_membuka_halaman_akun(): void
    {
        $analis = User::factory()->create();
        $analis->assignRole('analis_dinas');

        $this->actingAs($analis)->get(route('akun'))->assertRedirect(route('dinas.profil'));

        $kepala = User::factory()->create();
        $kepala->assignRole('kepala_sekolah');
        $this->actingAs($kepala)->get(route('akun'))->assertRedirect(route('sekolah.beranda'));
    }

    public function test_superadmin_tidak_dapat_membuka_halaman_data(): void
    {
        $this->withoutVite();
        $s = $this->superadmin();

        $this->actingAs($s)->get(route('dinas.profil'))->assertRedirect(route('akun'));
        $this->actingAs($s)->get(route('sekolah.beranda'))->assertRedirect(route('akun'));
        $this->actingAs($s)->get(route('panduan'))->assertRedirect(route('akun'));
        $this->actingAs($s)->get('/')->assertRedirect(route('akun'));
    }

    public function test_membuat_akun_analis_dinas(): void
    {
        Livewire::actingAs($this->superadmin())
            ->test(KelolaAkun::class)
            ->set('nama', 'Budi Santoso')
            ->set('email', 'budi@dinas.example')
            ->set('peran', 'analis_dinas')
            ->set('kataSandi', 'rahasia123')
            ->call('buatAkun')
            ->assertHasNoErrors();

        $user = User::where('email', 'budi@dinas.example')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('analis_dinas'));
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_email_ganda_ditolak(): void
    {
        User::factory()->create(['email' => 'ada@example.com']);

        Livewire::actingAs($this->superadmin())
            ->test(KelolaAkun::class)
            ->set('nama', 'Orang Baru')
            ->set('email', 'ada@example.com')
            ->set('peran', 'kepala_sekolah')
            ->set('kataSandi', 'rahasia123')
            ->call('buatAkun')
            ->assertHasErrors('email');
    }

    public function test_kata_sandi_pendek_ditolak(): void
    {
        Livewire::actingAs($this->superadmin())
            ->test(KelolaAkun::class)
            ->set('nama', 'Orang Baru')
            ->set('email', 'baru@example.com')
            ->set('peran', 'admin')
            ->set('kataSandi', 'pendek')
            ->call('buatAkun')
            ->assertHasErrors('kataSandi');
    }

    public function test_tidak_dapat_membuat_akun_superadmin_lain(): void
    {
        Livewire::actingAs($this->superadmin())
            ->test(KelolaAkun::class)
            ->set('nama', 'Super Dua')
            ->set('email', 'super2@example.com')
            ->set('peran', 'superadmin')
            ->set('kataSandi', 'rahasia123')
            ->call('buatAkun')
            ->assertHasErrors('peran');
    }

    public function test_menghapus_akun_biasa(): void
    {
        $target = User::factory()->create();
        $target->assignRole('analis_dinas');

        Livewire::actingAs($this->superadmin())
            ->test(KelolaAkun::class)
            ->call('konfirmasiHapus', $target->id)
            ->call('hapusAkun');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_tidak_dapat_menghapus_superadmin_atau_diri_sendiri(): void
    {
        $s = $this->superadmin();
        $lain = User::factory()->create();
        $lain->assignRole('superadmin');

        Livewire::actingAs($s)
            ->test(KelolaAkun::class)
            ->call('konfirmasiHapus', $lain->id)
            ->call('hapusAkun');
        $this->assertDatabaseHas('users', ['id' => $lain->id]);

        Livewire::actingAs($s)
            ->test(KelolaAkun::class)
            ->call('konfirmasiHapus', $s->id)
            ->call('hapusAkun');
        $this->assertDatabaseHas('users', ['id' => $s->id]);
    }
}

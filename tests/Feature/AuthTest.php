<?php

namespace Tests\Feature;

use App\Http\Livewire\Auth\Login;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(PeranSeeder::class);
    }

    private function pengguna(string $peran = 'analis_dinas'): User
    {
        $user = User::factory()->create([
            'email' => 'uji@akar.test',
            'password' => Hash::make('rahasia123'),
        ]);
        $user->assignRole($peran);

        return $user;
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('dinas.profil'))->assertRedirect(route('login'));
    }

    public function test_akar_diarahkan_sesuai_status_masuk(): void
    {
        $this->get('/')->assertRedirect(route('login'));

        $this->actingAs($this->pengguna())->get('/')->assertRedirect(route('dinas.profil'));
    }

    public function test_pengguna_masuk_dapat_membuka_halaman_dinas(): void
    {
        $this->actingAs($this->pengguna())
            ->get(route('dinas.profil'))
            ->assertOk()
            ->assertSee('Profil capaian daerah');
    }

    public function test_kredensial_benar_memasukkan_pengguna(): void
    {
        $this->pengguna();

        Livewire::test(Login::class)
            ->set('email', 'uji@akar.test')
            ->set('password', 'rahasia123')
            ->call('login')
            ->assertRedirect(route('dinas.profil'));

        $this->assertAuthenticated();
    }

    public function test_kredensial_salah_menampilkan_galat_dan_tetap_tamu(): void
    {
        $this->pengguna();

        Livewire::test(Login::class)
            ->set('email', 'uji@akar.test')
            ->set('password', 'salah')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_mengembalikan_ke_tamu(): void
    {
        $this->actingAs($this->pengguna());

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_pengguna_masuk_tidak_bisa_membuka_halaman_masuk(): void
    {
        $this->actingAs($this->pengguna())
            ->get(route('login'))
            ->assertRedirect();
    }

    public function test_percobaan_masuk_dibatasi_setelah_lima_kegagalan(): void
    {
        $this->pengguna();

        $komponen = Livewire::test(Login::class)->set('email', 'uji@akar.test')->set('password', 'salah');

        for ($i = 0; $i < 5; $i++) {
            $komponen->call('login');
        }

        $komponen->call('login')->assertHasErrors('email');

        $this->assertStringContainsString('Terlalu banyak percobaan', $komponen->errors()->first('email'));

        RateLimiter::clear('uji@akar.test|127.0.0.1');
    }
}

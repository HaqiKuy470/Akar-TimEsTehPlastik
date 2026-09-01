<?php

namespace Tests\Feature\Livewire;

use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanduanTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('panduan'))->assertRedirect(route('login'));
    }

    public function test_pengguna_masuk_dapat_membuka_panduan(): void
    {
        $this->withoutVite();

        $this->actingAs(User::factory()->create())
            ->get(route('panduan'))
            ->assertOk()
            ->assertSee('Panduan penggunaan AKAR')
            ->assertSee('Skor prioritas')
            ->assertSee('Akar masalah & tingkat keyakinan')
            ->assertSee('Sumber data & batasan');
    }

    public function test_kepala_sekolah_juga_dapat_membuka_panduan(): void
    {
        $this->withoutVite();
        $this->seed(PeranSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('kepala_sekolah');

        $this->actingAs($user)->get(route('panduan'))->assertOk();
    }

    public function test_bobot_skor_ditampilkan_dari_konfigurasi(): void
    {
        $this->withoutVite();

        config(['akar.bobot_komponen' => ['label' => 40, 'perubahan' => 25, 'posisi' => 20, 'turunan' => 15]]);

        $this->actingAs(User::factory()->create())
            ->get(route('panduan'))
            ->assertSee('Label capaian (40%)')
            ->assertSee('Posisi relatif (20%)');
    }
}

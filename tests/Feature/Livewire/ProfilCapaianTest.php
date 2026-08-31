<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Dinas\ProfilCapaian;
use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfilCapaianTest extends TestCase
{
    use RefreshDatabase;

    private Wilayah $wilayah;

    protected function setUp(): void
    {
        parent::setUp();

        $impor = ImporBerkas::factory()->create(['jenis' => 'daerah', 'tahun_edisi' => 2025, 'status' => 'selesai']);
        $this->wilayah = Wilayah::factory()->create(['level' => 'kabkota', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kabupaten Bangkalan']);

        $literasi = Indikator::factory()->nomor('A.1')->create([
            'nama' => 'Kemampuan literasi',
            'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH,
        ]);

        Capaian::factory()->create([
            'impor_id' => $impor->id,
            'wilayah_id' => $this->wilayah->id,
            'indikator_id' => $literasi->id,
            'tahun' => 2025,
            'jenis_satuan' => 'SMP Umum',
            'status_satuan' => 'Semua (Negeri dan Swasta)',
            'label_capaian' => 'Kurang',
            'perubahan_nilai' => 'Turun',
        ]);
    }

    public function test_halaman_dapat_dibuka(): void
    {
        $this->withoutVite();

        $this->actingAs(User::factory()->create())
            ->get(route('dinas.profil'))->assertOk()->assertSee('Profil capaian daerah');
    }

    public function test_tahun_terisi_otomatis_dari_edisi_terbaru(): void
    {
        Livewire::test(ProfilCapaian::class)->assertSet('tahun', 2025);
    }

    public function test_memilih_kombinasi_lengkap_menampilkan_indikator(): void
    {
        Livewire::test(ProfilCapaian::class)
            ->set('provinsi', 'Jawa Timur')
            ->set('wilayahId', $this->wilayah->id)
            ->set('jenisSatuan', 'SMP Umum')
            ->set('statusSatuan', 'Semua (Negeri dan Swasta)')
            ->assertSee('Kemampuan literasi')
            ->assertSee('Kurang');
    }

    public function test_mengganti_provinsi_mengosongkan_kabupaten_kota(): void
    {
        Livewire::test(ProfilCapaian::class)
            ->set('wilayahId', $this->wilayah->id)
            ->set('provinsi', 'Bali')
            ->assertSet('wilayahId', null);
    }
}

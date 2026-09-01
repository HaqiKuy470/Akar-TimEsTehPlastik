<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Dinas\ProfilCapaian;
use App\Http\Livewire\Dinas\SekolahDaerah;
use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SekolahDaerahTest extends TestCase
{
    use RefreshDatabase;

    private Wilayah $kabkota;

    private Wilayah $sekolah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kabkota = Wilayah::factory()->create([
            'level' => 'kabkota', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kota Surabaya',
        ]);

        $imporSekolah = ImporBerkas::factory()->create(['jenis' => 'satuan', 'status' => 'selesai']);
        $this->sekolah = Wilayah::factory()->create([
            'level' => 'satuan', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kota Surabaya',
            'nama_satuan' => 'SMP Negeri 1 Surabaya', 'induk_id' => $this->kabkota->id,
        ]);

        $literasi = Indikator::factory()->nomor('A.1')->create([
            'nama' => 'Kemampuan literasi', 'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH,
        ]);
        Capaian::factory()->create([
            'impor_id' => $imporSekolah->id,
            'wilayah_id' => $this->sekolah->id,
            'indikator_id' => $literasi->id,
            'tahun' => 2025,
            'jenis_satuan' => 'SMP',
            'status_satuan' => 'Negeri',
            'label_capaian' => 'Kurang',
            'perubahan_nilai' => 'Turun',
        ]);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('dinas.sekolah'))->assertRedirect(route('login'));
    }

    public function test_menampilkan_daftar_sekolah_di_kabupaten(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(SekolahDaerah::class, ['kabkota' => $this->kabkota->id])
            ->assertSee('SMP Negeri 1 Surabaya');
    }

    public function test_membuka_satu_sekolah_menampilkan_profilnya(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(SekolahDaerah::class, ['kabkota' => $this->kabkota->id, 'wilayah' => $this->sekolah->id])
            ->assertSee('Kemampuan literasi')
            ->assertSee('Kurang');
    }

    public function test_sekolah_di_luar_kabupaten_ditolak(): void
    {
        $lain = Wilayah::factory()->create(['level' => 'satuan', 'induk_id' => null, 'nama_satuan' => 'Sekolah Lain']);

        Livewire::actingAs(User::factory()->create())
            ->test(SekolahDaerah::class, ['kabkota' => $this->kabkota->id, 'wilayah' => $lain->id])
            ->assertDontSee('Sekolah Lain')
            ->assertSee('Pilih satu sekolah');
    }

    public function test_dinas_profil_menautkan_sekolah_di_kabupaten(): void
    {
        $imporDaerah = ImporBerkas::factory()->create(['jenis' => 'daerah', 'tahun_edisi' => 2025, 'status' => 'selesai']);
        $ind = Indikator::factory()->nomor('D.1')->create([
            'nama' => 'Kualitas pembelajaran', 'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH,
            'tersedia_kabkota' => true,
        ]);
        Capaian::factory()->create([
            'impor_id' => $imporDaerah->id, 'wilayah_id' => $this->kabkota->id, 'indikator_id' => $ind->id,
            'tahun' => 2025, 'jenis_satuan' => 'SMP Umum', 'status_satuan' => 'Semua (Negeri dan Swasta)',
            'label_capaian' => 'Sedang', 'perubahan_nilai' => 'Naik',
        ]);

        Livewire::actingAs(User::factory()->create())
            ->test(ProfilCapaian::class)
            ->set('tahun', 2025)
            ->set('provinsi', 'Jawa Timur')
            ->set('wilayahId', $this->kabkota->id)
            ->set('jenisSatuan', 'SMP Umum')
            ->set('statusSatuan', 'Semua (Negeri dan Swasta)')
            ->assertSee('SMP Negeri 1 Surabaya')
            ->assertSee('sudah mengunggah berkas');
    }
}

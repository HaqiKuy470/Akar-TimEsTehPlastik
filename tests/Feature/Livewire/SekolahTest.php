<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Sekolah\Beranda;
use App\Http\Livewire\Sekolah\Prioritas;
use App\Http\Livewire\Sekolah\ProfilCapaian;
use App\Http\Livewire\Sekolah\RencanaKerja;
use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\Akar\PemetaanJenisLayanan;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SekolahTest extends TestCase
{
    use RefreshDatabase;

    private const JENIS = 'SMP Negeri';

    private const STATUS = 'Negeri';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(PeranSeeder::class);
    }

    private function kepalaSekolah(): User
    {
        $u = User::factory()->create(['email' => 'kepala@uji.test', 'password' => Hash::make('rahasia123')]);
        $u->assignRole('kepala_sekolah');

        return $u;
    }

    private function sekolahBerdata(User $pengguna): Wilayah
    {
        $kab = Wilayah::factory()->create(['level' => 'kabkota', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kota Surabaya']);
        $sekolah = Wilayah::factory()->create([
            'level' => 'satuan',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kota Surabaya',
            'nama_satuan' => 'SMP Negeri 1 Surabaya',
            'induk_id' => $kab->id,
        ]);

        $impor = ImporBerkas::factory()->create([
            'jenis' => 'satuan',
            'tahun_edisi' => null,
            'status' => 'selesai',
            'diunggah_oleh' => $pengguna->id,
        ]);

        foreach ([['A.1', 'Kemampuan literasi', 'Kurang'], ['D.1', 'Kualitas pembelajaran', 'Sedang'], ['A.3', 'Karakter', 'Baik']] as [$nomor, $nama, $label]) {
            $ind = Indikator::factory()->nomor($nomor)->create([
                'nama' => $nama,
                'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH,
                'tersedia_satuan' => true,
            ]);
            Capaian::factory()->create([
                'impor_id' => $impor->id,
                'wilayah_id' => $sekolah->id,
                'indikator_id' => $ind->id,
                'tahun' => 2025,
                'jenis_satuan' => self::JENIS,
                'status_satuan' => self::STATUS,
                'label_capaian' => $label,
                'perubahan_nilai' => 'Turun',
            ]);
        }

        return $sekolah;
    }

    public function test_login_kepala_sekolah_diarahkan_ke_beranda_sekolah(): void
    {
        $this->kepalaSekolah();

        Livewire::test(Login::class)
            ->set('email', 'kepala@uji.test')
            ->set('password', 'rahasia123')
            ->call('login')
            ->assertRedirect(route('sekolah.beranda'));
    }

    public function test_kepala_sekolah_yang_membuka_area_dinas_diarahkan_ke_beranda_sekolah(): void
    {
        $this->actingAs($this->kepalaSekolah())
            ->get(route('dinas.profil'))
            ->assertRedirect(route('sekolah.beranda'));
    }

    public function test_beranda_tanpa_berkas_menampilkan_ajakan_unggah(): void
    {
        Livewire::actingAs($this->kepalaSekolah())
            ->test(Beranda::class)
            ->assertSee('Unggah data satuan pendidikan')
            ->assertSee(route('sekolah.unggah'));
    }

    public function test_beranda_dengan_data_menampilkan_nama_sekolah_dan_ringkasan(): void
    {
        $u = $this->kepalaSekolah();
        $this->sekolahBerdata($u);

        Livewire::actingAs($u)
            ->test(Beranda::class)
            ->assertSee('SMP Negeri 1 Surabaya')
            ->assertSee('Perlu perhatian');
    }

    public function test_profil_capaian_sekolah_menampilkan_indikator(): void
    {
        $u = $this->kepalaSekolah();
        $this->sekolahBerdata($u);

        Livewire::actingAs($u)
            ->test(ProfilCapaian::class)
            ->assertSee('Kemampuan literasi')
            ->assertSee('SMP Negeri 1 Surabaya');
    }

    public function test_prioritas_sekolah_menjalankan_analisis_dan_menampilkan_kartu(): void
    {
        $u = $this->kepalaSekolah();
        $this->sekolahBerdata($u);

        Livewire::actingAs($u)
            ->test(Prioritas::class)
            ->call('jalankan')
            ->assertSee('Kemampuan literasi')
            ->assertSee('Skor prioritas');
    }

    public function test_rkt_menyusun_draf_tanpa_galat(): void
    {
        $u = $this->kepalaSekolah();
        $this->sekolahBerdata($u);

        // Jalankan analisis lebih dulu lewat komponen prioritas.
        Livewire::actingAs($u)->test(Prioritas::class)->call('jalankan');

        Livewire::actingAs($u)
            ->test(RencanaKerja::class)
            ->call('susunDraf')
            ->assertSee('Rencana Kerja Tahunan');
    }

    public function test_sekolah_pengguna_hanya_melihat_berkasnya_sendiri(): void
    {
        $a = $this->kepalaSekolah();
        $this->sekolahBerdata($a);

        $b = User::factory()->create(['email' => 'lain@uji.test']);
        $b->assignRole('kepala_sekolah');

        Livewire::actingAs($b)
            ->test(Beranda::class)
            ->assertSee('Unggah data satuan pendidikan')
            ->assertDontSee('SMP Negeri 1 Surabaya');
    }
}

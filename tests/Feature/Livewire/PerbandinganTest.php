<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Dinas\Perbandingan;
use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class PerbandinganTest extends TestCase
{
    use RefreshDatabase;

    private const TAHUN = 2025;

    private const JENIS = 'SMP Umum';

    private const STATUS = 'Semua (Negeri dan Swasta)';

    private ImporBerkas $impor;

    private Indikator $indikator;

    private Wilayah $bangkalan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->impor = ImporBerkas::factory()->create(['jenis' => 'daerah', 'tahun_edisi' => self::TAHUN, 'status' => 'selesai']);
        $this->indikator = Indikator::factory()->nomor('A.1')->create([
            'nama' => 'Kemampuan literasi',
            'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH,
        ]);

        $prov = 'Jawa Timur';
        $this->bangkalan = $this->kabkota($prov, 'Kabupaten Bangkalan');
        $surabaya = $this->kabkota($prov, 'Kota Surabaya');
        $malang = $this->kabkota($prov, 'Kota Malang');
        $sampang = $this->kabkota($prov, 'Kabupaten Sampang');

        $this->capaian($this->bangkalan, 'Sedang', 'Turun');
        $this->capaian($surabaya, 'Baik', 'Naik');
        $this->capaian($malang, 'Sedang', 'Tidak berubah');
        $this->capaian($sampang, 'Kurang', 'Turun');

        $this->capaian(Wilayah::factory()->provinsi()->create(['provinsi' => $prov]), 'Sedang', 'Naik');
        $this->capaian(Wilayah::factory()->nasional()->create(), 'Sedang', 'Tidak berubah');
    }

    private function kabkota(string $provinsi, string $nama): Wilayah
    {
        return Wilayah::factory()->create(['level' => 'kabkota', 'provinsi' => $provinsi, 'kabupaten_kota' => $nama]);
    }

    private function capaian(Wilayah $wilayah, string $label, string $perubahan): void
    {
        Capaian::factory()->create([
            'impor_id' => $this->impor->id,
            'wilayah_id' => $wilayah->id,
            'indikator_id' => $this->indikator->id,
            'tahun' => self::TAHUN,
            'jenis_satuan' => self::JENIS,
            'status_satuan' => self::STATUS,
            'label_capaian' => $label,
            'perubahan_nilai' => $perubahan,
        ]);
    }

    private function komponenTerisi(): Testable
    {
        return Livewire::test(Perbandingan::class)
            ->set('provinsi', 'Jawa Timur')
            ->set('wilayahId', $this->bangkalan->id)
            ->set('jenisSatuan', self::JENIS)
            ->set('statusSatuan', self::STATUS)
            ->set('indikatorId', $this->indikator->id);
    }

    public function test_komponen_render_dengan_judul(): void
    {
        Livewire::test(Perbandingan::class)->assertOk()->assertSee('Bandingkan capaian satu kabupaten/kota');
    }

    public function test_tahun_terisi_otomatis_dari_edisi_terbaru(): void
    {
        Livewire::test(Perbandingan::class)->assertSet('tahun', self::TAHUN);
    }

    public function test_menampilkan_peringkat_dan_daerah_lain(): void
    {
        $this->komponenTerisi()
            ->assertSee('Kabupaten Bangkalan')
            ->assertSee('Kota Surabaya')
            ->assertSee('Kota Malang')
            ->assertSee('dari 4 kabupaten/kota')
            ->assertSee('Kemampuan literasi');
    }

    public function test_indikator_terisi_otomatis_bila_belum_dipilih(): void
    {
        Livewire::test(Perbandingan::class)
            ->set('provinsi', 'Jawa Timur')
            ->set('wilayahId', $this->bangkalan->id)
            ->set('jenisSatuan', self::JENIS)
            ->set('statusSatuan', self::STATUS)
            ->assertSee('A.1');
    }

    public function test_pembanding_provinsi_dan_nasional_tampil(): void
    {
        $this->komponenTerisi()
            ->assertSee('Agregat provinsi')
            ->assertSee('Nasional');
    }

    public function test_mengganti_provinsi_mengosongkan_kabupaten_kota(): void
    {
        Livewire::test(Perbandingan::class)
            ->set('wilayahId', $this->bangkalan->id)
            ->set('provinsi', 'Bali')
            ->assertSet('wilayahId', null);
    }

    public function test_klik_header_membalik_arah_pengurutan(): void
    {
        $this->komponenTerisi()
            ->assertSet('urutKolom', 'peringkat')
            ->assertSet('urutArah', 'asc')
            ->call('urutkan', 'peringkat')
            ->assertSet('urutArah', 'desc')
            ->call('urutkan', 'nama')
            ->assertSet('urutKolom', 'nama')
            ->assertSet('urutArah', 'asc');
    }

    public function test_wilayah_tanpa_data_menampilkan_catatan(): void
    {
        $ngawi = $this->kabkota('Jawa Timur', 'Kabupaten Ngawi');

        Livewire::test(Perbandingan::class)
            ->set('provinsi', 'Jawa Timur')
            ->set('wilayahId', $ngawi->id)
            ->set('jenisSatuan', self::JENIS)
            ->set('statusSatuan', self::STATUS)
            ->set('indikatorId', $this->indikator->id)
            ->assertSee('tidak masuk pemeringkatan');
    }
}

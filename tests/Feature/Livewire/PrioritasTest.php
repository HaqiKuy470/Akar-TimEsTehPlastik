<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Dinas\Prioritas;
use App\Models\Analisis;
use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PrioritasTest extends TestCase
{
    use RefreshDatabase;

    private const JENIS = 'SMP Umum';

    private const STATUS = 'Semua (Negeri dan Swasta)';

    private ImporBerkas $impor;

    private Wilayah $wilayah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->impor = ImporBerkas::factory()->create([
            'jenis' => 'daerah', 'tahun_edisi' => 2025, 'status' => 'selesai',
        ]);
        $this->wilayah = Wilayah::factory()->create([
            'level' => 'kabkota', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kabupaten Bangkalan',
        ]);
    }

    private function indikator(string $nomor, string $nama): Indikator
    {
        return Indikator::factory()->nomor($nomor)->create([
            'nama' => $nama,
            'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH,
            'tersedia_kabkota' => true,
        ]);
    }

    private function capaian(Indikator $indikator, string $label, string $perubahan, ?Wilayah $wilayah = null): void
    {
        Capaian::factory()->create([
            'impor_id' => $this->impor->id,
            'wilayah_id' => ($wilayah ?? $this->wilayah)->id,
            'indikator_id' => $indikator->id,
            'tahun' => 2025,
            'jenis_satuan' => self::JENIS,
            'status_satuan' => self::STATUS,
            'label_capaian' => $label,
            'perubahan_nilai' => $perubahan,
        ]);
    }

    private function komponen()
    {
        return Livewire::test(Prioritas::class)
            ->set('provinsi', 'Jawa Timur')
            ->set('wilayahId', $this->wilayah->id)
            ->set('jenisSatuan', self::JENIS)
            ->set('statusSatuan', self::STATUS);
    }

    public function test_komponen_dapat_dirender(): void
    {
        Livewire::test(Prioritas::class)
            ->assertOk()
            ->assertSee('perlu didahulukan')
            ->assertSee('Jalankan analisis');
    }

    public function test_tahun_terisi_otomatis(): void
    {
        Livewire::test(Prioritas::class)->assertSet('tahun', 2025);
    }

    public function test_sebelum_dijalankan_tidak_ada_kartu(): void
    {
        $this->capaian($this->indikator('A.1', 'Kemampuan literasi'), 'Kurang', 'Turun');

        $this->komponen()
            ->assertSee('Data sudah lengkap')
            ->assertDontSee('Skor prioritas');
    }

    public function test_menjalankan_analisis_menampilkan_kartu_prioritas(): void
    {
        $this->capaian($this->indikator('A.1', 'Kemampuan literasi'), 'Kurang', 'Turun');
        $this->capaian($this->indikator('A.2', 'Kemampuan numerasi'), 'Sedang', 'Naik');

        $this->komponen()
            ->call('jalankan')
            ->assertSee('A.1')
            ->assertSee('Kemampuan literasi')
            ->assertSee('Skor prioritas')
            ->assertSee('daftar prioritas');

        $this->assertDatabaseCount('analisis', 1);
        $this->assertDatabaseHas('analisis_prioritas', ['peringkat' => 1]);
    }

    public function test_kalimat_penjelas_selalu_tampil(): void
    {
        $this->capaian($this->indikator('A.1', 'Kemampuan literasi'), 'Kurang', 'Turun');

        $analisis = $this->komponen()->call('jalankan');
        $penjelas = Analisis::first()->prioritas()->first()->kalimat_penjelas;

        $this->assertNotEmpty($penjelas);
        $analisis->assertSee($penjelas);
    }

    public function test_rincian_skor_dapat_dibuka(): void
    {
        $this->capaian($this->indikator('A.1', 'Kemampuan literasi'), 'Kurang', 'Turun');

        $c = $this->komponen()->call('jalankan');
        $id = Analisis::first()->prioritas()->first()->id;

        $c->assertDontSee('Posisi relatif terhadap daerah lain')
            ->call('toggleRincian', $id)
            ->assertSee('Posisi relatif terhadap daerah lain')
            ->assertSee('dari 40');
    }

    public function test_telusuri_akar_untuk_indikator_yang_dipetakan(): void
    {
        // A.1 dipetakan di config/intervensi.php; D.2 (Kurang) menjadi bukti.
        $literasi = $this->indikator('A.1', 'Kemampuan literasi');
        $refleksi = $this->indikator('D.2', 'Refleksi dan perbaikan pembelajaran oleh guru');
        $this->capaian($literasi, 'Kurang', 'Turun');
        $this->capaian($refleksi, 'Kurang', 'Turun');

        $c = $this->komponen()->call('jalankan');
        $id = Analisis::first()->prioritas()->where('indikator_id', $literasi->id)->first()->id;

        $c->call('toggleAkar', $id)
            ->assertSee('bukti')
            ->assertSee('D.2');

        $this->assertDatabaseHas('analisis_akar', ['analisis_prioritas_id' => $id]);
    }

    public function test_telusuri_akar_untuk_indikator_yang_belum_dipetakan(): void
    {
        $takDipetakan = $this->indikator('E.7.1', 'Indeks fasilitas ruang');
        $this->capaian($takDipetakan, 'Kurang', 'Turun');

        $c = $this->komponen()->call('jalankan');
        $id = Analisis::first()->prioritas()->first()->id;

        $c->call('toggleAkar', $id)
            ->assertSee('Rekomendasi akar masalah belum tersedia');

        $this->assertDatabaseCount('analisis_akar', 0);
    }

    public function test_analisis_tanpa_indikator_bermasalah_memberi_pesan_positif(): void
    {
        $this->capaian($this->indikator('A.1', 'Kemampuan literasi'), 'Baik', 'Naik');

        $this->komponen()
            ->call('jalankan')
            ->assertSee('Tidak ditemukan indikator bermasalah');
    }

    public function test_mengganti_wilayah_menutup_panel(): void
    {
        $this->capaian($this->indikator('A.1', 'Kemampuan literasi'), 'Kurang', 'Turun');
        $lain = Wilayah::factory()->create([
            'level' => 'kabkota', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kabupaten Sampang',
        ]);

        $c = $this->komponen()->call('jalankan');
        $id = Analisis::first()->prioritas()->first()->id;

        $c->call('toggleRincian', $id)
            ->assertSet('rincianTerbuka', [$id])
            ->set('wilayahId', $lain->id)
            ->assertSet('rincianTerbuka', []);
    }
}

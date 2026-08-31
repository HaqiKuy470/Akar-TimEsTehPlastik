<?php

namespace Tests\Feature;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\Analysis\ProfilCapaianService;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilCapaianServiceTest extends TestCase
{
    use RefreshDatabase;

    private const JENIS = 'SMP Umum';

    private const STATUS = 'Semua (Negeri dan Swasta)';

    private ImporBerkas $impor;

    private Wilayah $wilayah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->impor = ImporBerkas::factory()->create(['jenis' => 'daerah', 'tahun_edisi' => 2025, 'status' => 'selesai']);
        $this->wilayah = Wilayah::factory()->create(['level' => 'kabkota', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kabupaten Bangkalan']);
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

    private function profil(): array
    {
        return app(ProfilCapaianService::class)->untukWilayah($this->wilayah, 2025, self::JENIS, self::STATUS);
    }

    public function test_tanpa_impor_selesai_mengembalikan_profil_kosong(): void
    {
        $this->impor->update(['status' => 'proses']);

        $profil = $this->profil();

        $this->assertFalse($profil['tersedia']);
        $this->assertSame(0, $profil['ringkasan']['total']);
    }

    public function test_meringkas_jumlah_merah_kuning_hijau(): void
    {
        $this->capaian($this->indikator('A.1', 'Kemampuan literasi'), 'Kurang', 'Turun');
        $this->capaian($this->indikator('A.2', 'Kemampuan numerasi'), 'Sedang', 'Naik');
        $this->capaian($this->indikator('D.1', 'Kualitas pembelajaran'), 'Baik', 'Tidak berubah');

        $ringkasan = $this->profil()['ringkasan'];

        $this->assertSame(1, $ringkasan['merah']);
        $this->assertSame(1, $ringkasan['kuning']);
        $this->assertSame(1, $ringkasan['hijau']);
        $this->assertSame(3, $ringkasan['total']);
    }

    public function test_mengelompokkan_indikator_menurut_dimensi_induk(): void
    {
        $this->capaian($this->indikator('A.1', 'Kemampuan literasi'), 'Kurang', 'Turun');
        $this->capaian($this->indikator('D.1', 'Kualitas pembelajaran'), 'Sedang', 'Naik');

        $dimensi = $this->profil()['dimensi'];

        $this->assertSame(['A', 'D'], array_keys($dimensi));
        $this->assertSame('A.1', $dimensi['A']['indikator'][0]['nomor']);
        $this->assertNotEmpty($dimensi['A']['nama']);
    }

    public function test_indikator_kolom_sheet_tanpa_baris_untuk_wilayah_menjadi_tidak_tersedia(): void
    {
        // Indikator ini menjadi kolom di sheet (punya baris untuk wilayah lain
        // dalam impor yang sama) tetapi tidak untuk wilayah terpilih.
        $lain = Wilayah::factory()->create(['provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kabupaten Sampang']);
        $adaTapiKosong = $this->indikator('E.1', 'Partisipasi warga sekolah');
        $this->capaian($adaTapiKosong, 'Baik', 'Naik', $lain);

        $this->capaian($this->indikator('A.1', 'Kemampuan literasi'), 'Kurang', 'Turun');

        $profil = $this->profil();

        $this->assertSame(1, $profil['ringkasan']['tidak_tersedia']);
        $this->assertSame('E.1', $profil['tidak_tersedia'][0]['nomor']);
        $this->assertSame('Tidak Tersedia', $profil['tidak_tersedia'][0]['label_capaian']);
        // Tidak ikut dalam kelompok dimensi.
        $this->assertArrayNotHasKey('E', $profil['dimensi']);
    }

    public function test_indikator_di_luar_jenis_layanan_tidak_ikut(): void
    {
        $paud = Indikator::factory()->nomor('A.1')->create([
            'nama' => 'Capaian perkembangan anak',
            'jenis_layanan' => PemetaanJenisLayanan::PAUD,
        ]);
        $this->capaian($paud, 'Kurang', 'Turun');

        $profil = $this->profil();

        $this->assertSame(0, $profil['ringkasan']['total']);
    }

    public function test_menyertakan_definisi_ambang_dan_arah_perubahan(): void
    {
        $this->capaian($this->indikator('A.1', 'Kemampuan literasi'), 'Kurang', 'Turun');

        $entri = $this->profil()['dimensi']['A']['indikator'][0];

        $this->assertSame('Turun', $entri['perubahan_nilai']);
        $this->assertStringContainsString('40%', $entri['ambang']['merah']);
        $this->assertSame('merah', $entri['status']);
    }

    public function test_indikator_diurutkan_secara_natural(): void
    {
        $this->capaian($this->indikator('A.10', 'Sepuluh'), 'Baik', 'Naik');
        $this->capaian($this->indikator('A.2', 'Dua'), 'Baik', 'Naik');

        $nomor = array_column($this->profil()['dimensi']['A']['indikator'], 'nomor');

        $this->assertSame(['A.2', 'A.10'], $nomor);
    }
}

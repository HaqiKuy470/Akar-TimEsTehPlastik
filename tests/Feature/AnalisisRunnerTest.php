<?php

namespace Tests\Feature;

use App\Models\Analisis;
use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\Analysis\AnalisisRunner;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalisisRunnerTest extends TestCase
{
    use RefreshDatabase;

    private const JENIS = 'SMP Umum';

    private const STATUS = 'Semua (Negeri dan Swasta)';

    private ImporBerkas $impor;

    private Wilayah $bangkalan;

    private Wilayah $sampang;

    private Wilayah $sumenep;

    private Indikator $a1;

    private Indikator $a1a;

    private Indikator $a1b;

    private Indikator $d2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->impor = ImporBerkas::factory()->create(['jenis' => 'daerah', 'tahun_edisi' => 2025, 'status' => 'selesai']);

        $this->bangkalan = Wilayah::factory()->create(['level' => 'kabkota', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kabupaten Bangkalan']);
        $this->sampang = Wilayah::factory()->create(['level' => 'kabkota', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kabupaten Sampang']);
        $this->sumenep = Wilayah::factory()->create(['level' => 'kabkota', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kabupaten Sumenep']);

        $this->a1 = Indikator::factory()->nomor('A.1')->create(['nama' => 'Kemampuan literasi', 'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH]);
        $this->a1a = Indikator::factory()->nomor('A.1.1')->create(['nama' => 'Kompetensi membaca teks informasi', 'induk_id' => $this->a1->id, 'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH]);
        $this->a1b = Indikator::factory()->nomor('A.1.2')->create(['nama' => 'Kompetensi membaca teks sastra', 'induk_id' => $this->a1->id, 'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH]);
        $this->d2 = Indikator::factory()->nomor('D.2')->create(['nama' => 'Refleksi dan perbaikan pembelajaran', 'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH]);
    }

    private function capaian(Wilayah $wilayah, Indikator $indikator, string $label, string $perubahan): void
    {
        Capaian::factory()->create([
            'impor_id' => $this->impor->id,
            'wilayah_id' => $wilayah->id,
            'indikator_id' => $indikator->id,
            'tahun' => 2025,
            'jenis_satuan' => self::JENIS,
            'status_satuan' => self::STATUS,
            'label_capaian' => $label,
            'perubahan_nilai' => $perubahan,
        ]);
    }

    private function siapkanData(): void
    {
        // Bangkalan: kondisi terburuk pada A.1.
        $this->capaian($this->bangkalan, $this->a1, 'Kurang', 'Turun');
        $this->capaian($this->bangkalan, $this->a1a, 'Kurang', 'Turun');
        $this->capaian($this->bangkalan, $this->a1b, 'Sedang', 'Tidak berubah');
        $this->capaian($this->bangkalan, $this->d2, 'Sedang', 'Naik');

        // Pembanding di provinsi yang sama.
        $this->capaian($this->sampang, $this->a1, 'Baik', 'Naik');
        $this->capaian($this->sampang, $this->d2, 'Baik', 'Naik');
        $this->capaian($this->sumenep, $this->a1, 'Sedang', 'Tidak berubah');
        $this->capaian($this->sumenep, $this->d2, 'Kurang', 'Turun');
    }

    private function jalankan(): Analisis
    {
        return app(AnalisisRunner::class)->jalankan($this->bangkalan, 2025, self::JENIS, self::STATUS);
    }

    public function test_menyimpan_analisis_dengan_salinan_bobot(): void
    {
        $this->siapkanData();

        $analisis = $this->jalankan();

        $this->assertDatabaseHas('analisis', ['id' => $analisis->id, 'wilayah_id' => $this->bangkalan->id]);
        $this->assertArrayHasKey('bobot_komponen', $analisis->bobot_dipakai);
        $this->assertSame(40, $analisis->bobot_dipakai['bobot_komponen']['label']);
    }

    public function test_hanya_indikator_bermasalah_yang_diberi_skor(): void
    {
        $this->siapkanData();
        // Indikator berlabel Baik untuk Bangkalan tidak boleh masuk.
        $e1 = Indikator::factory()->nomor('E.1')->create(['jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH]);
        $this->capaian($this->bangkalan, $e1, 'Baik', 'Naik');

        $analisis = $this->jalankan();

        $this->assertSame(4, $analisis->prioritas->count());
        $this->assertNotContains($e1->id, $analisis->prioritas->pluck('indikator_id')->all());
    }

    public function test_indikator_terparah_menempati_peringkat_satu_dengan_skor_maksimum(): void
    {
        $this->siapkanData();

        $analisis = $this->jalankan();
        $teratas = $analisis->prioritas->firstWhere('peringkat', 1);

        $this->assertSame($this->a1->id, $teratas->indikator_id);
        $this->assertSame('100.00', (string) $teratas->skor);
    }

    public function test_peringkat_urut_menurun_menurut_skor(): void
    {
        $this->siapkanData();

        $skor = $this->jalankan()->prioritas->sortBy('peringkat')->pluck('skor')->map(fn ($s) => (float) $s)->all();

        $terurut = $skor;
        rsort($terurut);
        $this->assertSame($terurut, $skor);
    }

    public function test_komponen_skor_tersimpan_sebagai_rincian(): void
    {
        $this->siapkanData();

        $teratas = $this->jalankan()->prioritas->firstWhere('peringkat', 1);

        $this->assertIsArray($teratas->komponen_skor);
        $this->assertCount(4, $teratas->komponen_skor);
        $this->assertSame('label', $teratas->komponen_skor[0]['kode']);
    }

    public function test_kalimat_penjelas_selalu_terisi(): void
    {
        $this->siapkanData();

        $analisis = $this->jalankan();

        foreach ($analisis->prioritas as $prioritas) {
            $this->assertNotEmpty($prioritas->kalimat_penjelas);
        }

        $teratas = $analisis->prioritas->firstWhere('peringkat', 1);
        $this->assertStringContainsString('peringkat 3 dari 3', $teratas->kalimat_penjelas);
        $this->assertStringContainsString('2 dari 2 indikator turunan', $teratas->kalimat_penjelas);
    }

    public function test_tanpa_indikator_bermasalah_menghasilkan_analisis_kosong(): void
    {
        $this->capaian($this->bangkalan, $this->a1, 'Baik', 'Naik');

        $analisis = $this->jalankan();

        $this->assertSame(0, $analisis->prioritas->count());
        $this->assertDatabaseHas('analisis', ['id' => $analisis->id]);
    }
}

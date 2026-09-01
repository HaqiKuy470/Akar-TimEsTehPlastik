<?php

namespace Tests\Feature;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\Analysis\TrenService;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrenServiceTest extends TestCase
{
    use RefreshDatabase;

    private const JENIS = 'SMP Umum';

    private const STATUS = 'Semua (Negeri dan Swasta)';

    private Wilayah $wilayah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wilayah = Wilayah::factory()->create([
            'level' => 'kabkota', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kabupaten Bangkalan',
        ]);
    }

    private function indikator(string $nomor, string $nama): Indikator
    {
        return Indikator::factory()->nomor($nomor)->create([
            'nama' => $nama, 'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH,
        ]);
    }

    /**
     * @param  array<int, string>  $labelPerTahun
     */
    private function deret(Indikator $indikator, array $labelPerTahun): void
    {
        foreach ($labelPerTahun as $tahun => $label) {
            $impor = ImporBerkas::factory()->create(['jenis' => 'daerah', 'tahun_edisi' => $tahun, 'status' => 'selesai']);
            Capaian::factory()->create([
                'impor_id' => $impor->id,
                'wilayah_id' => $this->wilayah->id,
                'indikator_id' => $indikator->id,
                'tahun' => $tahun,
                'jenis_satuan' => self::JENIS,
                'status_satuan' => self::STATUS,
                'label_capaian' => $label,
                'perubahan_nilai' => 'Tidak berubah',
            ]);
        }
    }

    private function tren(): array
    {
        return app(TrenService::class)->untukWilayah($this->wilayah, self::JENIS, self::STATUS);
    }

    public function test_satu_tahun_saja_menandai_cukup_tahun_false(): void
    {
        $this->deret($this->indikator('A.1', 'Literasi'), [2025 => 'Kurang']);

        $tren = $this->tren();

        $this->assertFalse($tren['cukup_tahun']);
        $this->assertSame([2025], $tren['tahun']);
    }

    public function test_mendeteksi_memburuk_dua_tahun_berturut(): void
    {
        $this->deret($this->indikator('A.1', 'Literasi'), [2023 => 'Baik', 2024 => 'Sedang', 2025 => 'Kurang']);

        $tren = $this->tren();

        $this->assertTrue($tren['cukup_tahun']);
        $this->assertSame(1, $tren['ringkasan']['memburuk_berturut']);
        $this->assertSame('A.1', $tren['memburuk'][0]['nomor']);
        $this->assertSame(['Baik', 'Sedang', 'Kurang'], array_values($tren['memburuk'][0]['deret']));
    }

    public function test_sekali_turun_belum_dihitung_memburuk_berturut(): void
    {
        $this->deret($this->indikator('A.1', 'Literasi'), [2023 => 'Baik', 2024 => 'Baik', 2025 => 'Sedang']);

        $tren = $this->tren();

        $this->assertSame(0, $tren['ringkasan']['memburuk_berturut']);
    }

    public function test_mendeteksi_membaik_konsisten(): void
    {
        $this->deret($this->indikator('D.1', 'Kualitas pembelajaran'), [2023 => 'Kurang', 2024 => 'Sedang', 2025 => 'Baik']);

        $tren = $this->tren();

        $this->assertSame(1, $tren['ringkasan']['membaik_konsisten']);
        $this->assertSame('D.1', $tren['membaik'][0]['nomor']);
    }

    public function test_tahun_tanpa_baris_menjadi_tidak_tersedia(): void
    {
        // Edisi 2023 ada untuk indikator lain, tapi A.1 baru muncul 2024.
        $this->deret($this->indikator('B.1', 'Lain'), [2023 => 'Baik']);
        $this->deret($this->indikator('A.1', 'Literasi'), [2024 => 'Sedang', 2025 => 'Kurang']);

        $tren = $this->tren();
        $a1 = collect($tren['dimensi']['A']['indikator'])->firstWhere('nomor', 'A.1');

        $this->assertSame('Tidak Tersedia', $a1['deret'][2023]);
        $this->assertNull($a1['nilai'][0]);
    }

    public function test_seri_grafik_memuat_indikator_dan_warna(): void
    {
        $this->deret($this->indikator('A.1', 'Literasi'), [2023 => 'Baik', 2024 => 'Sedang', 2025 => 'Kurang']);

        $grafik = $this->tren()['grafik'];

        $this->assertSame(['2023', '2024', '2025'], $grafik['tahun']);
        $this->assertSame('A.1', $grafik['seri'][0]['nomor']);
        $this->assertSame('#b4231a', $grafik['seri'][0]['warna']); // isian grafik "memburuk"
        $this->assertSame([3, 2, 1], $grafik['seri'][0]['nilai']);
    }

    public function test_mengelompokkan_menurut_dimensi(): void
    {
        $this->deret($this->indikator('A.1', 'Literasi'), [2024 => 'Sedang', 2025 => 'Sedang']);
        $this->deret($this->indikator('D.2', 'Refleksi'), [2024 => 'Kurang', 2025 => 'Kurang']);

        $dimensi = $this->tren()['dimensi'];

        $this->assertSame(['A', 'D'], array_keys($dimensi));
        $this->assertNotEmpty($dimensi['A']['nama']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\Analysis\BenchmarkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BenchmarkServiceTest extends TestCase
{
    use RefreshDatabase;

    private const TAHUN = 2025;

    private const JENIS = 'SMP Umum';

    private const STATUS = 'Semua (Negeri dan Swasta)';

    private ImporBerkas $impor;

    private Indikator $indikator;

    private BenchmarkService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->impor = ImporBerkas::factory()->create(['jenis' => 'daerah', 'tahun_edisi' => self::TAHUN]);
        $this->indikator = Indikator::factory()->nomor('A.1')->create(['nama' => 'Kemampuan literasi']);
        $this->service = app(BenchmarkService::class);
    }

    private function kabkota(string $provinsi, string $nama): Wilayah
    {
        return Wilayah::factory()->create([
            'level' => 'kabkota',
            'provinsi' => $provinsi,
            'kabupaten_kota' => $nama,
        ]);
    }

    private function capaian(Wilayah $wilayah, string $label, string $perubahan = 'Tidak berubah'): void
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

    private function peringkat(Wilayah $w): array
    {
        return $this->service->peringkat($w, $this->indikator, self::TAHUN, self::JENIS, self::STATUS);
    }

    public function test_peringkat_dihitung_dari_jumlah_daerah_berlabel_lebih_baik(): void
    {
        $baik1 = $this->kabkota('Jawa Timur', 'Kota Surabaya');
        $baik2 = $this->kabkota('Jawa Timur', 'Kota Malang');
        $sedang = $this->kabkota('Jawa Timur', 'Kabupaten Bangkalan');
        $kurang = $this->kabkota('Jawa Timur', 'Kabupaten Sampang');

        $this->capaian($baik1, 'Baik');
        $this->capaian($baik2, 'Baik');
        $this->capaian($sedang, 'Sedang');
        $this->capaian($kurang, 'Kurang');

        $hasil = $this->peringkat($sedang);

        $this->assertSame('Sedang', $hasil['label_wilayah']);
        $this->assertSame(3, $hasil['peringkat']);       // 2 Baik di atasnya
        $this->assertSame(3, $hasil['peringkat_hingga']); // hanya dia yang Sedang
        $this->assertSame(4, $hasil['dari']);
        $this->assertNull($hasil['catatan']);
    }

    public function test_daerah_berlabel_sama_berbagi_rentang_peringkat(): void
    {
        $prov = 'Bali';
        $a = $this->kabkota($prov, 'Kabupaten Badung');
        $b = $this->kabkota($prov, 'Kabupaten Gianyar');
        $c = $this->kabkota($prov, 'Kabupaten Tabanan');

        $this->capaian($a, 'Baik');
        $this->capaian($b, 'Sedang');
        $this->capaian($c, 'Sedang');

        $hasil = $this->peringkat($b);

        $this->assertSame(2, $hasil['peringkat']);
        $this->assertSame(3, $hasil['peringkat_hingga']);
        $this->assertSame(3, $hasil['dari']);
    }

    public function test_label_tidak_tersedia_dikeluarkan_dari_populasi(): void
    {
        $prov = 'Aceh';
        $baik = $this->kabkota($prov, 'Kota Banda Aceh');
        $sedang = $this->kabkota($prov, 'Kabupaten Pidie');
        $kosong = $this->kabkota($prov, 'Kabupaten Simeulue');

        $this->capaian($baik, 'Baik');
        $this->capaian($sedang, 'Sedang');
        $this->capaian($kosong, 'Tidak Tersedia');

        $hasil = $this->peringkat($sedang);

        $this->assertSame(2, $hasil['peringkat']);
        $this->assertSame(2, $hasil['dari']); // hanya Baik + Sedang, bukan 3
    }

    public function test_persentil_terbaik_mendekati_satu_terburuk_mendekati_nol(): void
    {
        $prov = 'Jawa Barat';
        $daerah = [];
        foreach (['Baik', 'Baik', 'Sedang', 'Kurang', 'Kurang'] as $i => $label) {
            $daerah[$i] = $this->kabkota($prov, "Kabupaten $i");
            $this->capaian($daerah[$i], $label);
        }

        $atas = $this->peringkat($daerah[0]);
        $bawah = $this->peringkat($daerah[4]);

        $this->assertGreaterThan($bawah['persentil'], $atas['persentil']);
        $this->assertGreaterThanOrEqual(0.0, $bawah['persentil']);
        $this->assertLessThanOrEqual(1.0, $atas['persentil']);
    }

    public function test_wilayah_tanpa_data_tidak_diperingkat_tetapi_populasi_dilaporkan(): void
    {
        $prov = 'Jawa Timur';
        $ada = $this->kabkota($prov, 'Kota Kediri');
        $tanpa = $this->kabkota($prov, 'Kabupaten Ngawi');
        $this->capaian($ada, 'Baik');

        $hasil = $this->peringkat($tanpa);

        $this->assertNull($hasil['peringkat']);
        $this->assertSame(1, $hasil['dari']);
        $this->assertNotNull($hasil['catatan']);
    }

    public function test_pemeringkatan_hanya_dalam_provinsi_yang_sama(): void
    {
        $jatim = $this->kabkota('Jawa Timur', 'Kota Surabaya');
        $jabar = $this->kabkota('Jawa Barat', 'Kota Bandung');
        $this->capaian($jatim, 'Sedang');
        $this->capaian($jabar, 'Baik');

        $hasil = $this->peringkat($jatim);

        // Daerah Jawa Barat tidak ikut, jadi Sedang menjadi peringkat 1 dari 1.
        $this->assertSame(1, $hasil['peringkat']);
        $this->assertSame(1, $hasil['dari']);
    }

    public function test_tabel_peringkat_terurut_label_terbaik_dulu(): void
    {
        $prov = 'Jawa Timur';
        $this->capaian($this->kabkota($prov, 'Kabupaten Sampang'), 'Kurang');
        $this->capaian($this->kabkota($prov, 'Kota Surabaya'), 'Baik');
        $this->capaian($this->kabkota($prov, 'Kabupaten Bangkalan'), 'Sedang');
        $this->capaian($this->kabkota($prov, 'Kabupaten Tuban'), 'Baik', 'Naik');

        $tabel = $this->service->tabelPeringkat($prov, $this->indikator, self::TAHUN, self::JENIS, self::STATUS);

        $this->assertCount(4, $tabel);
        $this->assertSame(['Baik', 'Baik', 'Sedang', 'Kurang'], array_column($tabel, 'label_capaian'));
        $this->assertSame(1, $tabel[0]['peringkat']);
        $this->assertSame(1, $tabel[1]['peringkat']);
        $this->assertSame(3, $tabel[2]['peringkat']); // 2 Baik di atas
        $this->assertSame(4, $tabel[3]['peringkat']);
    }

    public function test_pembanding_menyandingkan_wilayah_provinsi_dan_nasional(): void
    {
        $prov = 'Jawa Timur';
        $kab = $this->kabkota($prov, 'Kabupaten Bangkalan');
        $agregatProvinsi = Wilayah::factory()->provinsi()->create(['provinsi' => $prov]);
        $nasional = Wilayah::factory()->nasional()->create();

        $this->capaian($kab, 'Kurang', 'Turun');
        $this->capaian($agregatProvinsi, 'Sedang', 'Naik');
        $this->capaian($nasional, 'Sedang', 'Tidak berubah');

        $hasil = $this->service->pembanding($kab, $this->indikator, self::TAHUN, self::JENIS, self::STATUS);

        $this->assertSame('Kurang', $hasil['wilayah']['label']);
        $this->assertSame('Sedang', $hasil['provinsi']['label']);
        $this->assertTrue($hasil['provinsi']['tersedia']);
        $this->assertSame('Sedang', $hasil['nasional']['label']);
        $this->assertTrue($hasil['nasional']['tersedia']);
    }

    public function test_pembanding_menyatakan_agregat_nasional_tidak_tersedia_bila_tak_ada(): void
    {
        $kab = $this->kabkota('Bali', 'Kabupaten Badung');
        $this->capaian($kab, 'Baik');

        $hasil = $this->service->pembanding($kab, $this->indikator, self::TAHUN, self::JENIS, self::STATUS);

        $this->assertFalse($hasil['provinsi']['tersedia']);
        $this->assertFalse($hasil['nasional']['tersedia']);
        $this->assertNull($hasil['nasional']['label']);
    }
}

<?php

namespace Tests\Feature;

use App\Jobs\ProsesImporBerkas;
use App\Jobs\ProsesSheetProvinsi;
use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Services\Akar\Parsers\CapaianDaerahParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ProsesImporBerkasTest extends TestCase
{
    use RefreshDatabase;

    private string $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = __DIR__.'/../Fixtures/sheet_provinsi_mini.xlsx';

        // Indikator yang dipakai fixture (biasanya dari berkas Metadata).
        foreach ([
            ['A.1', 'Kemampuan literasi', 'A', 'Pendidikan Dasar dan Pendidikan Menengah'],
            ['A.1.1', 'Kompetensi membaca teks informasi', 'A', 'Pendidikan Dasar dan Pendidikan Menengah'],
            ['D.2', 'Refleksi dan perbaikan pembelajaran oleh guru', 'D', 'Pendidikan Dasar dan Pendidikan Menengah'],
            ['D.2', 'Proses belajar yang sesuai bagi anak usia dini', 'D', 'Pendidikan Anak Usia Dini'],
        ] as [$nomor, $nama, $dimensi, $jenis]) {
            Indikator::create([
                'nomor' => $nomor, 'nama' => $nama, 'dimensi' => $dimensi,
                'jenis_layanan' => $jenis, 'tersedia_kabkota' => true, 'tersedia_provinsi' => true,
            ]);
        }
    }

    private function jalankan(): void
    {
        (new ProsesImporBerkas($this->fixture))->handle(app(CapaianDaerahParser::class));
    }

    public function test_memecah_impor_menjadi_satu_job_per_sheet_provinsi(): void
    {
        Bus::fake();

        $this->jalankan();

        // Fixture punya dua sheet provinsi: Jawa Timur dan Bali.
        Bus::assertBatched(function ($batch) {
            return $batch->jobs->count() === 2
                && $batch->jobs->every(fn ($j) => $j instanceof ProsesSheetProvinsi);
        });
    }

    public function test_mencatat_berkas_dan_menyetel_tahun_edisi_sekali_di_awal(): void
    {
        Bus::fake();

        $this->jalankan();

        $impor = ImporBerkas::sole();
        $this->assertSame('daerah', $impor->jenis);
        $this->assertSame(2025, $impor->tahun_edisi);
        $this->assertSame('proses', $impor->status);
    }

    public function test_idempoten_berkas_yang_sudah_selesai_tidak_diproses_ulang(): void
    {
        ImporBerkas::factory()->create([
            'hash_berkas' => hash_file('sha256', $this->fixture),
            'status' => 'selesai',
        ]);

        Bus::fake();
        $this->jalankan();

        Bus::assertNothingBatched();
        $this->assertSame(1, ImporBerkas::count());
    }

    public function test_alur_penuh_mengisi_capaian_dan_menandai_selesai(): void
    {
        $this->jalankan();

        $impor = ImporBerkas::sole();
        $this->assertSame('selesai', $impor->status);
        $this->assertNotNull($impor->diproses_pada);
        $this->assertGreaterThan(0, $impor->jumlah_baris);
        $this->assertGreaterThan(0, Capaian::where('impor_id', $impor->id)->count());
    }

    public function test_menjalankan_ulang_tidak_menggandakan_capaian(): void
    {
        $this->jalankan();
        $jumlah = Capaian::count();

        (new ProsesImporBerkas($this->fixture))->handle(app(CapaianDaerahParser::class));

        $this->assertSame($jumlah, Capaian::count());
        $this->assertSame(1, ImporBerkas::count());
    }

    public function test_selesaikan_menandai_gagal_hanya_bila_semua_sheet_gagal(): void
    {
        $impor = ImporBerkas::factory()->create(['status' => 'proses', 'catatan_galat' => null]);

        ProsesImporBerkas::selesaikan($impor->id, gagal: 3, total: 3);
        $this->assertSame('gagal', $impor->fresh()->status);
    }

    public function test_selesaikan_tetap_selesai_bila_sebagian_sheet_gagal(): void
    {
        $impor = ImporBerkas::factory()->create(['status' => 'proses', 'catatan_galat' => null]);

        ProsesImporBerkas::selesaikan($impor->id, gagal: 1, total: 3);

        $segar = $impor->fresh();
        $this->assertSame('selesai', $segar->status);
        $this->assertStringContainsString('1 dari 3 sheet gagal', (string) $segar->catatan_galat);
    }

    public function test_sheet_gagal_mencatat_pesan_di_catatan_galat(): void
    {
        $impor = ImporBerkas::factory()->create(['catatan_galat' => null]);

        $job = new ProsesSheetProvinsi($this->fixture, 'Sheet Tidak Ada', $impor->id, 2025);
        $job->failed(new \RuntimeException('Sheet tidak ditemukan.'));

        $this->assertStringContainsString('Sheet Tidak Ada: Sheet tidak ditemukan.', (string) $impor->fresh()->catatan_galat);
    }
}

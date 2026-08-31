<?php

namespace Tests\Feature;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\Parsers\CapaianSekolahParser;
use App\Services\Akar\Parsers\DeteksiJenisBerkas;
use App\Services\Akar\Parsers\HeaderResolver;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * Mode satuan pendidikan (F10). Struktur berkas asli belum diverifikasi tim;
 * uji ini mengunci perilaku terhadap fixture yang mewakili dugaan struktur.
 */
class CapaianSekolahParserTest extends TestCase
{
    use RefreshDatabase;

    private string $fixtureSekolah;

    private string $fixtureTakDikenal;

    private string $fixtureDaerah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureSekolah = __DIR__.'/../Fixtures/berkas_sekolah_mini.xlsx';
        $this->fixtureTakDikenal = __DIR__.'/../Fixtures/berkas_tak_dikenal.xlsx';
        $this->fixtureDaerah = __DIR__.'/../Fixtures/sheet_provinsi_mini.xlsx';

        foreach ([
            ['A.1', 'Kemampuan literasi'],
            ['A.1.1', 'Kompetensi membaca teks informasi'],
            ['D.4', 'Iklim keamanan satuan pendidikan'],
        ] as [$nomor, $nama]) {
            Indikator::factory()->nomor($nomor)->create([
                'nama' => $nama,
                'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH,
                'tersedia_satuan' => true,
            ]);
        }
    }

    private function parser(): CapaianSekolahParser
    {
        return new CapaianSekolahParser(new HeaderResolver);
    }

    // --- DeteksiJenisBerkas -------------------------------------------------

    public function test_deteksi_mengenali_berkas_satuan(): void
    {
        $this->assertSame(
            DeteksiJenisBerkas::SATUAN,
            (new DeteksiJenisBerkas)->untuk($this->fixtureSekolah)
        );
    }

    public function test_deteksi_mengenali_berkas_daerah(): void
    {
        $this->assertSame(
            DeteksiJenisBerkas::DAERAH,
            (new DeteksiJenisBerkas)->untuk($this->fixtureDaerah)
        );
    }

    public function test_deteksi_menolak_berkas_asing(): void
    {
        $this->assertSame(
            DeteksiJenisBerkas::TIDAK_DIKENAL,
            (new DeteksiJenisBerkas)->untuk($this->fixtureTakDikenal)
        );
    }

    // --- CapaianSekolahParser --------------------------------------------

    public function test_impor_membuat_wilayah_level_satuan(): void
    {
        $this->parser()->impor($this->fixtureSekolah);

        $satuan = Wilayah::where('level', 'satuan')->first();
        $this->assertNotNull($satuan);
        $this->assertStringContainsString('SD NEGERI PERCOBAAN SURABAYA', $satuan->nama_satuan);
        $this->assertSame('Jawa Timur', $satuan->provinsi);
        $this->assertSame('Kota Surabaya', $satuan->kabupaten_kota);

        // Terhubung ke wilayah kabupaten/kota sebagai induk (untuk pembanding F5).
        $kab = Wilayah::where('level', 'kabkota')->where('kabupaten_kota', 'Kota Surabaya')->first();
        $this->assertNotNull($kab);
        $this->assertSame($kab->id, $satuan->induk_id);
    }

    public function test_impor_menyimpan_capaian_dan_melewati_tidak_tersedia(): void
    {
        $impor = $this->parser()->impor($this->fixtureSekolah);

        $this->assertSame('satuan', $impor->jenis);
        $this->assertSame('selesai', $impor->status);
        $this->assertGreaterThan(0, Capaian::count());
        $this->assertSame(0, Capaian::where('label_capaian', 'Tidak Tersedia')->count());

        $literasi = Indikator::where('nomor', 'A.1')->first();
        $satuan = Wilayah::where('level', 'satuan')->first();
        $capaian = Capaian::where('wilayah_id', $satuan->id)
            ->where('indikator_id', $literasi->id)
            ->where('status_satuan', 'Negeri')
            ->first();
        $this->assertSame('Kurang', $capaian->label_capaian);
        $this->assertSame('Turun', $capaian->perubahan_nilai);
        $this->assertSame(2025, $capaian->tahun);
    }

    public function test_impor_idempoten(): void
    {
        $this->parser()->impor($this->fixtureSekolah);
        $jumlah = Capaian::count();
        $this->parser()->impor($this->fixtureSekolah);

        $this->assertSame($jumlah, Capaian::count());
        $this->assertSame(1, ImporBerkas::count());
    }

    public function test_nama_satuan_dapat_ditimpa_argumen(): void
    {
        $this->parser()->impor($this->fixtureSekolah, 'SMP Negeri 1 Malang');

        $this->assertTrue(Wilayah::where('level', 'satuan')->where('nama_satuan', 'SMP Negeri 1 Malang')->exists());
    }

    public function test_berkas_asing_ditolak_dengan_pesan_jelas(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Label Capaian|tidak sesuai/');

        $this->parser()->impor($this->fixtureTakDikenal);
    }

    public function test_berkas_asing_menandai_impor_gagal(): void
    {
        $impor = ImporBerkas::factory()->create(['jenis' => 'satuan', 'status' => 'antre']);

        try {
            $this->parser()->imporKe($impor, $this->fixtureTakDikenal);
        } catch (RuntimeException) {
            // diharapkan
        }

        $this->assertSame('gagal', $impor->fresh()->status);
        $this->assertNotNull($impor->fresh()->catatan_galat);
    }
}

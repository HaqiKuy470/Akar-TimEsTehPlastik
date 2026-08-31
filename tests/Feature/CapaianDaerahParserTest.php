<?php

namespace Tests\Feature;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\Parsers\CapaianDaerahParser;
use App\Services\Akar\Parsers\HeaderResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CapaianDaerahParserTest extends TestCase
{
    use RefreshDatabase;

    private string $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = __DIR__.'/../Fixtures/sheet_provinsi_mini.xlsx';

        // Indikator biasanya diisi dari berkas Metadata. Di sini cukup empat
        // yang dipakai fixture, termasuk dua "D.2" dengan nama berbeda untuk
        // menguji disambiguasi PAUD vs dasar-menengah.
        $this->buatIndikator('A.1', 'Kemampuan literasi', 'A', 'Pendidikan Dasar dan Pendidikan Menengah');
        $this->buatIndikator('A.1.1', 'Kompetensi membaca teks informasi', 'A', 'Pendidikan Dasar dan Pendidikan Menengah');
        $this->buatIndikator('D.2', 'Refleksi dan perbaikan pembelajaran oleh guru', 'D', 'Pendidikan Dasar dan Pendidikan Menengah');
        $this->buatIndikator('D.2', 'Proses belajar yang sesuai bagi anak usia dini', 'D', 'Pendidikan Anak Usia Dini');
    }

    private function buatIndikator(string $nomor, string $nama, string $dimensi, string $jenisLayanan): Indikator
    {
        return Indikator::create([
            'nomor' => $nomor,
            'nama' => $nama,
            'dimensi' => $dimensi,
            'jenis_layanan' => $jenisLayanan,
            'tersedia_kabkota' => true,
            'tersedia_provinsi' => true,
        ]);
    }

    private function parser(): CapaianDaerahParser
    {
        return new CapaianDaerahParser(new HeaderResolver);
    }

    public function test_impor_mencatat_berkas_dan_mendeteksi_tahun_dari_isi(): void
    {
        $impor = $this->parser()->impor($this->fixture);

        $this->assertSame('daerah', $impor->jenis);
        $this->assertSame(2025, $impor->tahun_edisi);
        $this->assertSame('selesai', $impor->status);
        $this->assertNotNull($impor->diproses_pada);
    }

    public function test_baris_agregat_provinsi_disimpan_sebagai_wilayah_level_provinsi(): void
    {
        $this->parser()->impor($this->fixture);

        $jatim = Wilayah::where('level', 'provinsi')->where('provinsi', 'Jawa Timur')->first();
        $this->assertNotNull($jatim);

        $malang = Wilayah::where('level', 'kabkota')->where('kabupaten_kota', 'Kabupaten Malang')->first();
        $this->assertNotNull($malang);
        $this->assertSame($jatim->id, $malang->induk_id);
    }

    public function test_dua_provinsi_di_fixture_terbaca(): void
    {
        $this->parser()->impor($this->fixture);

        $this->assertSame(2, Wilayah::where('level', 'provinsi')->count());
        $this->assertTrue(Wilayah::where('provinsi', 'Bali')->where('level', 'kabkota')->exists());
    }

    public function test_capaian_tidak_tersedia_tidak_disimpan(): void
    {
        $this->parser()->impor($this->fixture);

        $this->assertSame(0, Capaian::where('label_capaian', 'Tidak Tersedia')->count());
        $this->assertGreaterThan(0, Capaian::count());
    }

    public function test_d2_dipetakan_sesuai_nama_kolom_bukan_hanya_nomor(): void
    {
        $this->parser()->impor($this->fixture);

        $d2Paud = Indikator::where('nomor', 'D.2')->where('jenis_layanan', 'Pendidikan Anak Usia Dini')->first();
        $d2Dasmen = Indikator::where('nomor', 'D.2')->where('jenis_layanan', 'Pendidikan Dasar dan Pendidikan Menengah')->first();

        // Baris PAUD Kabupaten Gresik: hanya kolom D.2 varian PAUD yang terisi "Kurang".
        $gresik = Wilayah::where('kabupaten_kota', 'Kabupaten Gresik')->first();
        $capaianGresik = Capaian::where('wilayah_id', $gresik->id)->get();

        $this->assertCount(1, $capaianGresik);
        $this->assertSame($d2Paud->id, $capaianGresik->first()->indikator_id);
        $this->assertSame('Kurang', $capaianGresik->first()->label_capaian);

        // Baris SD Umum (agregat Jawa Timur): kolom D.2 varian dasar-menengah "Kurang".
        $jatim = Wilayah::where('level', 'provinsi')->where('provinsi', 'Jawa Timur')->first();
        $this->assertTrue(
            Capaian::where('wilayah_id', $jatim->id)->where('indikator_id', $d2Dasmen->id)->exists()
        );
    }

    public function test_nilai_label_dan_perubahan_terbaca_benar(): void
    {
        $this->parser()->impor($this->fixture);

        $jatim = Wilayah::where('level', 'provinsi')->where('provinsi', 'Jawa Timur')->first();
        $a1 = Indikator::where('nomor', 'A.1')->first();

        $capaian = Capaian::where('wilayah_id', $jatim->id)->where('indikator_id', $a1->id)->first();
        $this->assertSame('Kurang', $capaian->label_capaian);
        $this->assertSame('Turun', $capaian->perubahan_nilai);
        $this->assertSame(2025, $capaian->tahun);
        $this->assertSame('SD Umum', $capaian->jenis_satuan);
    }

    public function test_impor_idempoten_untuk_hash_berkas_sama(): void
    {
        $parser = $this->parser();
        $parser->impor($this->fixture);
        $jumlahPertama = Capaian::count();

        $parser2 = $this->parser();
        $parser2->impor($this->fixture);

        $this->assertSame($jumlahPertama, Capaian::count());
        $this->assertSame(1, ImporBerkas::count());
    }

    public function test_impor_sheet_tunggal(): void
    {
        $impor = ImporBerkas::factory()->create(['jenis' => 'daerah']);

        $jumlah = $this->parser()->imporSheet($this->fixture, 'Bali', $impor, 2025);

        $this->assertSame(2, $jumlah); // dua baris data di sheet Bali
        $this->assertTrue(Wilayah::where('provinsi', 'Bali')->exists());
    }
}

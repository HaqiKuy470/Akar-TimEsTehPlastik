<?php

namespace Tests\Unit;

use App\Services\Akar\Parsers\MetadataIndikatorParser;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MetadataIndikatorParserTest extends TestCase
{
    private string $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = __DIR__.'/../Fixtures/metadata_mini.csv';
    }

    public function test_membaca_seluruh_baris_data_dan_melewati_baris_kosong(): void
    {
        $hasil = (new MetadataIndikatorParser)->parse($this->fixture);

        // Fixture memiliki 7 baris data plus satu baris kosong di tengah.
        $this->assertCount(7, $hasil);
    }

    public function test_dimensi_diambil_dari_huruf_pertama_nomor(): void
    {
        $hasil = (new MetadataIndikatorParser)->parse($this->fixture);

        $this->assertSame('A', $hasil[0]['dimensi']);
        $this->assertSame('A.1', $hasil[0]['nomor']);
        $this->assertSame('D', $hasil[3]['dimensi']);
    }

    public function test_nomor_berakhiran_skor_tetap_diterima(): void
    {
        $hasil = (new MetadataIndikatorParser)->parse($this->fixture);
        $skor = array_values(array_filter($hasil, fn ($b) => $b['nomor'] === 'A.1.skor'));

        $this->assertCount(1, $skor);
        $this->assertSame('A', $skor[0]['dimensi']);
    }

    public function test_ketersediaan_dipetakan_ke_boolean(): void
    {
        $hasil = (new MetadataIndikatorParser)->parse($this->fixture);

        $this->assertTrue($hasil[0]['tersedia_satuan']);
        $this->assertFalse($hasil[1]['tersedia_satuan']); // A.1.skor -> "Tidak"
        $this->assertTrue($hasil[1]['tersedia_kabkota']);
    }

    public function test_nilai_strip_dianggap_kosong(): void
    {
        $hasil = (new MetadataIndikatorParser)->parse($this->fixture);

        $this->assertNull($hasil[1]['label_merah']);      // A.1.skor Label Merah "-"
        $this->assertNull($hasil[1]['definisi_merah']);
    }

    public function test_label_merah_resmi_dipertahankan_apa_adanya(): void
    {
        $hasil = (new MetadataIndikatorParser)->parse($this->fixture);

        $this->assertSame('Kurang (xx% peserta didik)', $hasil[0]['label_merah']);
    }

    public function test_berkas_hilang_melempar_pengecualian(): void
    {
        $this->expectException(RuntimeException::class);
        (new MetadataIndikatorParser)->parse(__DIR__.'/tidak-ada.csv');
    }

    public function test_kolom_wajib_hilang_melempar_pengecualian_dengan_nama_kolom(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'meta').'.csv';
        file_put_contents($path, "Nomor Indikator,Nama Indikator\nA.1,Literasi\n");

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessageMatches('/Jenis Layanan/');
            (new MetadataIndikatorParser)->parse($path);
        } finally {
            @unlink($path);
        }
    }
}

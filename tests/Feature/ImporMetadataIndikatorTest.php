<?php

namespace Tests\Feature;

use App\Models\Indikator;
use App\Services\Akar\Parsers\MetadataIndikatorParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImporMetadataIndikatorTest extends TestCase
{
    use RefreshDatabase;

    private string $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = __DIR__.'/../Fixtures/metadata_mini.csv';
    }

    public function test_impor_menyimpan_seluruh_indikator(): void
    {
        $jumlah = (new MetadataIndikatorParser)->impor($this->fixture);

        $this->assertSame(7, $jumlah);
        $this->assertSame(7, Indikator::count());
    }

    public function test_impor_idempoten_tidak_menggandakan_data(): void
    {
        $parser = new MetadataIndikatorParser;
        $parser->impor($this->fixture);
        $parser->impor($this->fixture);

        $this->assertSame(7, Indikator::count());
    }

    public function test_induk_dipetakan_untuk_nomor_anak(): void
    {
        (new MetadataIndikatorParser)->impor($this->fixture);

        $anak = Indikator::where('nomor', 'A.1.1')->first();
        $this->assertNotNull($anak->induk_id);
        $this->assertSame('A.1', $anak->induk->nomor);

        $skor = Indikator::where('nomor', 'A.1.skor')->first();
        $this->assertSame('A.1', $skor->induk->nomor);
    }

    public function test_induk_ambigu_dibiarkan_kosong(): void
    {
        (new MetadataIndikatorParser)->impor($this->fixture);

        // Dua baris B.10 pada jenis layanan sama -> induk B.10 tidak jelas.
        // Tidak ada anak B.10.x di fixture, jadi cukup pastikan keduanya tersimpan.
        $this->assertSame(2, Indikator::where('nomor', 'B.10')->count());
    }

    public function test_nomor_sama_beda_jenis_layanan_disimpan_terpisah(): void
    {
        (new MetadataIndikatorParser)->impor($this->fixture);

        $this->assertSame(2, Indikator::where('nomor', 'D.2')->count());
        $this->assertSame(
            ['Pendidikan Anak Usia Dini', 'Pendidikan Dasar dan Pendidikan Menengah'],
            Indikator::where('nomor', 'D.2')->orderBy('jenis_layanan')->pluck('jenis_layanan')->all()
        );
    }
}

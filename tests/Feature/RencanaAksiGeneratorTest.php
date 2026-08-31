<?php

namespace Tests\Feature;

use App\Enums\Keyakinan;
use App\Models\Analisis;
use App\Models\AnalisisAkar;
use App\Models\AnalisisPrioritas;
use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\Output\RencanaAksiGenerator;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RencanaAksiGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private const JENIS = 'SMP Umum';

    private const STATUS = 'Semua (Negeri dan Swasta)';

    private Analisis $analisis;

    private Wilayah $wilayah;

    private ImporBerkas $impor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->impor = ImporBerkas::factory()->create(['jenis' => 'daerah', 'tahun_edisi' => 2025, 'status' => 'selesai']);
        $this->wilayah = Wilayah::factory()->create([
            'level' => 'kabkota', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kabupaten Bangkalan',
        ]);
        $this->analisis = Analisis::factory()->create([
            'wilayah_id' => $this->wilayah->id,
            'tahun' => 2025,
            'jenis_satuan' => self::JENIS,
            'status_satuan' => self::STATUS,
        ]);
    }

    private function prioritasDenganAkar(string $nomor, string $kodeAkar, Keyakinan $keyakinan, string $label = 'Kurang'): AnalisisPrioritas
    {
        $indikator = Indikator::factory()->nomor($nomor)->create([
            'nama' => 'Indikator '.$nomor,
            'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH,
        ]);

        Capaian::factory()->create([
            'impor_id' => $this->impor->id,
            'wilayah_id' => $this->wilayah->id,
            'indikator_id' => $indikator->id,
            'tahun' => 2025,
            'jenis_satuan' => self::JENIS,
            'status_satuan' => self::STATUS,
            'label_capaian' => $label,
            'perubahan_nilai' => 'Turun',
        ]);

        $prioritas = AnalisisPrioritas::factory()->create([
            'analisis_id' => $this->analisis->id,
            'indikator_id' => $indikator->id,
        ]);

        AnalisisAkar::factory()->keyakinan($keyakinan)->create([
            'analisis_prioritas_id' => $prioritas->id,
            'kode_akar' => $kodeAkar,
            'label' => 'Akar: '.$kodeAkar,
        ]);

        return $prioritas;
    }

    private function generator(): RencanaAksiGenerator
    {
        return app(RencanaAksiGenerator::class);
    }

    public function test_menghasilkan_satu_item_per_kegiatan_pada_kandidat_yang_cocok(): void
    {
        // A.1 / kandidat 'kualitas_pembelajaran' memiliki tiga kegiatan di config.
        $this->prioritasDenganAkar('A.1', 'kualitas_pembelajaran', Keyakinan::Kuat);

        $rencana = $this->generator()->hasilkan($this->analisis);

        // Kunci 'A.1' memuat titik, jadi tak bisa diakses lewat notasi titik config().
        $kegiatan = config('intervensi')['A.1']['kandidat_akar'][0]['kegiatan'];
        $this->assertSame('kualitas_pembelajaran', config('intervensi')['A.1']['kandidat_akar'][0]['kode']);
        $this->assertCount(count($kegiatan), $rencana->item);

        $pertama = $rencana->item->first();
        $this->assertSame('Indikator A.1 berlabel Kurang', $pertama->masalah);
        $this->assertSame('Akar: kualitas_pembelajaran', $pertama->akar_masalah);
        $this->assertNotEmpty($pertama->penanggung_jawab);
        $this->assertNotEmpty($pertama->perkiraan_waktu);
    }

    public function test_indikator_di_luar_config_intervensi_dilewati(): void
    {
        $this->prioritasDenganAkar('A.9', 'apa_saja', Keyakinan::Kuat);

        $rencana = $this->generator()->hasilkan($this->analisis);

        $this->assertCount(0, $rencana->item);
    }

    public function test_akar_tidak_cukup_bukti_dilewati(): void
    {
        $this->prioritasDenganAkar('A.1', 'kualitas_pembelajaran', Keyakinan::TidakCukupBukti);

        $rencana = $this->generator()->hasilkan($this->analisis);

        $this->assertCount(0, $rencana->item);
    }

    public function test_kandidat_dengan_kode_tak_dikenal_dilewati(): void
    {
        $this->prioritasDenganAkar('A.1', 'kode_yang_tidak_ada_di_config', Keyakinan::Kuat);

        $rencana = $this->generator()->hasilkan($this->analisis);

        $this->assertCount(0, $rencana->item);
    }

    public function test_idempoten_tidak_menggandakan_dan_menjaga_suntingan(): void
    {
        $this->prioritasDenganAkar('A.1', 'kualitas_pembelajaran', Keyakinan::Kuat);

        $pertama = $this->generator()->hasilkan($this->analisis);
        $jumlah = $pertama->item->count();
        $pertama->item->first()->update(['masalah' => 'Sudah disunting pengguna']);

        $kedua = $this->generator()->hasilkan($this->analisis);

        $this->assertSame($pertama->id, $kedua->id);
        $this->assertCount($jumlah, $kedua->fresh('item')->item);
        $this->assertSame('Sudah disunting pengguna', $kedua->fresh('item')->item->first()->masalah);
    }

    public function test_paksa_ulang_membangun_kembali_item(): void
    {
        $this->prioritasDenganAkar('A.1', 'kualitas_pembelajaran', Keyakinan::Kuat);

        $rencana = $this->generator()->hasilkan($this->analisis);
        $rencana->item->first()->update(['masalah' => 'Disunting']);

        $ulang = $this->generator()->hasilkan($this->analisis, null, paksaUlang: true);

        $this->assertNotSame('Disunting', $ulang->item->first()->masalah);
    }

    public function test_judul_default_memuat_nama_wilayah_dan_tahun(): void
    {
        $this->prioritasDenganAkar('A.1', 'kualitas_pembelajaran', Keyakinan::Kuat);

        $rencana = $this->generator()->hasilkan($this->analisis);

        $this->assertStringContainsString('Kabupaten Bangkalan', $rencana->judul);
        $this->assertStringContainsString('2025', $rencana->judul);
    }
}

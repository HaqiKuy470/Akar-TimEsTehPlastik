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
use App\Services\Akar\Analysis\AkarMasalahAnalyzer;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class AkarMasalahAnalyzerTest extends TestCase
{
    use RefreshDatabase;

    private const JENIS = 'SMP Umum';

    private const STATUS = 'Semua (Negeri dan Swasta)';

    private ImporBerkas $impor;

    private Wilayah $wilayah;

    private AkarMasalahAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->impor = ImporBerkas::factory()->create(['jenis' => 'daerah', 'tahun_edisi' => 2025, 'status' => 'selesai']);
        $this->wilayah = Wilayah::factory()->create(['level' => 'kabkota', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kabupaten Bangkalan']);
        $this->analyzer = app(AkarMasalahAnalyzer::class);
    }

    private function analisis(): Analisis
    {
        return Analisis::factory()->create([
            'wilayah_id' => $this->wilayah->id,
            'tahun' => 2025,
            'jenis_satuan' => self::JENIS,
            'status_satuan' => self::STATUS,
        ]);
    }

    private function indikator(string $nomor, string $nama): Indikator
    {
        return Indikator::factory()->nomor($nomor)->create([
            'nama' => $nama,
            'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH,
        ]);
    }

    private function prioritas(Analisis $analisis, string $nomor, string $nama): AnalisisPrioritas
    {
        return AnalisisPrioritas::factory()->create([
            'analisis_id' => $analisis->id,
            'indikator_id' => $this->indikator($nomor, $nama)->id,
        ]);
    }

    /**
     * Daftarkan satu entri pohon keputusan tambahan untuk pengujian.
     * config() memakai titik sebagai pemisah kedalaman, jadi kunci indikator
     * yang mengandung titik ("D.90") tidak bisa di-set langsung; seluruh
     * larik 'intervensi' ditulis ulang.
     *
     * @param  array<string, mixed>  $entri
     */
    private function daftarkanIntervensi(string $nomor, array $entri): void
    {
        config(['intervensi' => array_merge(config('intervensi'), [$nomor => $entri])]);
    }

    /** Buat indikator pendukung sekaligus baris capaiannya untuk analisis ini. */
    private function pendukung(Analisis $analisis, string $nomor, string $label): void
    {
        Capaian::factory()->create([
            'impor_id' => $this->impor->id,
            'wilayah_id' => $analisis->wilayah_id,
            'indikator_id' => $this->indikator($nomor, 'Pendukung '.$nomor)->id,
            'tahun' => $analisis->tahun,
            'jenis_satuan' => $analisis->jenis_satuan,
            'status_satuan' => $analisis->status_satuan,
            'label_capaian' => $label,
            'perubahan_nilai' => 'Tidak berubah',
        ]);
    }

    public function test_indikator_tak_dipetakan_mengembalikan_koleksi_kosong(): void
    {
        $analisis = $this->analisis();
        // D.77 tidak ada di config/intervensi.php.
        $prioritas = $this->prioritas($analisis, 'D.77', 'Indikator belum dipetakan');

        $hasil = $this->analyzer->telusuri($prioritas);

        $this->assertTrue($hasil->isEmpty());
        $this->assertSame(0, AnalisisAkar::count());
    }

    public function test_dua_pendukung_kurang_menghasilkan_keyakinan_kuat(): void
    {
        $analisis = $this->analisis();
        $prioritas = $this->prioritas($analisis, 'C.3', 'Pengalaman pelatihan PTK');
        // Kandidat tunggal C.3 memeriksa D.2, D.3, D.3.3 (ambang minimal_satu_kurang).
        $this->pendukung($analisis, 'D.2', 'Kurang');
        $this->pendukung($analisis, 'D.3', 'Kurang');

        $hasil = $this->analyzer->telusuri($prioritas);

        $this->assertCount(1, $hasil);
        $this->assertSame(Keyakinan::Kuat, $hasil->first()->keyakinan);
        $this->assertSame('program_pengembangan_belum_terjadwal', $hasil->first()->kode_akar);
    }

    public function test_satu_pendukung_kurang_menghasilkan_keyakinan_sedang(): void
    {
        $analisis = $this->analisis();
        $prioritas = $this->prioritas($analisis, 'C.3', 'Pengalaman pelatihan PTK');
        $this->pendukung($analisis, 'D.2', 'Kurang');
        $this->pendukung($analisis, 'D.3', 'Baik');

        $hasil = $this->analyzer->telusuri($prioritas);

        $this->assertSame(Keyakinan::Sedang, $hasil->first()->keyakinan);
    }

    public function test_gerbang_tidak_terlewati_menghasilkan_tidak_cukup_bukti(): void
    {
        $analisis = $this->analisis();
        $prioritas = $this->prioritas($analisis, 'C.3', 'Pengalaman pelatihan PTK');
        // Semua pendukung Baik: ambang minimal_satu_kurang tidak terlewati.
        $this->pendukung($analisis, 'D.2', 'Baik');
        $this->pendukung($analisis, 'D.3', 'Baik');

        $hasil = $this->analyzer->telusuri($prioritas);

        $this->assertCount(1, $hasil);
        $this->assertSame(Keyakinan::TidakCukupBukti, $hasil->first()->keyakinan);
        $this->assertSame([], $hasil->first()->bukti);
    }

    public function test_pendukung_tanpa_baris_capaian_dianggap_tidak_tersedia(): void
    {
        $analisis = $this->analisis();
        $prioritas = $this->prioritas($analisis, 'C.3', 'Pengalaman pelatihan PTK');
        // Tidak ada satu pun baris capaian pendukung.

        $hasil = $this->analyzer->telusuri($prioritas);

        $this->assertSame(Keyakinan::TidakCukupBukti, $hasil->first()->keyakinan);
    }

    public function test_dua_sedang_dan_satu_sedang_lewat_gerbang_mayoritas(): void
    {
        // config baru hanya memakai minimal_satu_kurang; suntikkan kandidat
        // ber-ambang mayoritas_bermasalah untuk menguji jalur keyakinan Sedang
        // (dua Sedang) dan Lemah (satu Sedang).
        $this->daftarkanIntervensi('D.90', [
            'nama' => 'Uji mayoritas',
            'kandidat_akar' => [[
                'kode' => 'uji', 'label' => 'Akar uji', 'periksa' => ['U.1', 'U.2', 'U.3'],
                'ambang' => 'mayoritas_bermasalah', 'kegiatan' => ['uji'],
            ]],
        ]);

        $analisis = $this->analisis();
        $prioritas = $this->prioritas($analisis, 'D.90', 'Uji mayoritas');
        $this->pendukung($analisis, 'U.1', 'Sedang');
        $this->pendukung($analisis, 'U.2', 'Sedang');
        $this->pendukung($analisis, 'U.3', 'Baik');

        $this->assertSame(Keyakinan::Sedang, $this->analyzer->telusuri($prioritas->fresh())->first()->keyakinan);
    }

    public function test_satu_sedang_lewat_gerbang_mayoritas_menghasilkan_lemah(): void
    {
        $this->daftarkanIntervensi('D.91', [
            'nama' => 'Uji lemah',
            'kandidat_akar' => [[
                'kode' => 'uji', 'label' => 'Akar uji', 'periksa' => ['V.1', 'V.2'],
                'ambang' => 'mayoritas_bermasalah', 'kegiatan' => ['uji'],
            ]],
        ]);

        $analisis = $this->analisis();
        $prioritas = $this->prioritas($analisis, 'D.91', 'Uji lemah');
        // Hanya V.1 yang punya data (Sedang); V.2 Tidak Tersedia -> di luar populasi.
        $this->pendukung($analisis, 'V.1', 'Sedang');

        $hasil = $this->analyzer->telusuri($prioritas);

        $this->assertSame(Keyakinan::Lemah, $hasil->first()->keyakinan);
    }

    public function test_hanya_kandidat_yang_lolos_gerbang_menjadi_baris(): void
    {
        $analisis = $this->analisis();
        $prioritas = $this->prioritas($analisis, 'A.1', 'Kemampuan literasi');
        // Kandidat 1 (periksa D.1, D.1.1, D.1.3) lolos; kandidat 2 & 3 tidak.
        $this->pendukung($analisis, 'D.1', 'Kurang');

        $hasil = $this->analyzer->telusuri($prioritas);

        $this->assertCount(1, $hasil);
        $this->assertSame('kualitas_pembelajaran', $hasil->first()->kode_akar);
    }

    public function test_hasil_diurutkan_dari_keyakinan_terkuat(): void
    {
        $analisis = $this->analisis();
        $prioritas = $this->prioritas($analisis, 'A.1', 'Kemampuan literasi');
        // Kandidat 1 -> kuat (2 Kurang); kandidat 2 -> sedang (1 Kurang).
        $this->pendukung($analisis, 'D.1', 'Kurang');
        $this->pendukung($analisis, 'D.1.1', 'Kurang');
        $this->pendukung($analisis, 'D.2', 'Kurang');

        $hasil = $this->analyzer->telusuri($prioritas);

        $this->assertSame(
            [Keyakinan::Kuat, Keyakinan::Sedang],
            $hasil->pluck('keyakinan')->all(),
        );
    }

    public function test_bukti_hanya_memuat_pendukung_kurang_atau_sedang(): void
    {
        $analisis = $this->analisis();
        $prioritas = $this->prioritas($analisis, 'C.3', 'Pengalaman pelatihan PTK');
        $this->pendukung($analisis, 'D.2', 'Kurang');
        $this->pendukung($analisis, 'D.3', 'Sedang');
        $this->pendukung($analisis, 'D.3.3', 'Baik');

        $bukti = $this->analyzer->telusuri($prioritas)->first()->bukti;

        $this->assertCount(2, $bukti);
        $this->assertEqualsCanonicalizing(['D.2', 'D.3'], array_column($bukti, 'nomor'));
        $this->assertSame('Kurang', $bukti[0]['label']);
    }

    public function test_penelusuran_idempoten(): void
    {
        $analisis = $this->analisis();
        $prioritas = $this->prioritas($analisis, 'C.3', 'Pengalaman pelatihan PTK');
        $this->pendukung($analisis, 'D.2', 'Kurang');

        $this->analyzer->telusuri($prioritas);
        $this->analyzer->telusuri($prioritas);

        $this->assertSame(1, AnalisisAkar::where('analisis_prioritas_id', $prioritas->id)->count());
    }

    public function test_telusuri_analisis_memproses_semua_prioritas(): void
    {
        $analisis = $this->analisis();
        $p1 = $this->prioritas($analisis, 'C.1', 'Proporsi PTK bersertifikat');
        $p2 = $this->prioritas($analisis, 'A.3', 'Karakter');
        $this->pendukung($analisis, 'C.3', 'Kurang'); // pendukung kandidat C.1
        $this->pendukung($analisis, 'D.1', 'Kurang'); // pendukung kandidat A.3

        $hasil = $this->analyzer->telusuriAnalisis($analisis);

        $this->assertGreaterThanOrEqual(2, $hasil->count());
        $this->assertNotEmpty($p1->akar()->get());
        $this->assertNotEmpty($p2->akar()->get());
    }

    public function test_ambang_tidak_dikenali_melempar_pengecualian(): void
    {
        $this->daftarkanIntervensi('E.90', [
            'nama' => 'Uji ambang',
            'kandidat_akar' => [[
                'kode' => 'x', 'label' => 'X', 'periksa' => ['Z.1'],
                'ambang' => 'ngawur', 'kegiatan' => [],
            ]],
        ]);

        $analisis = $this->analisis();
        $prioritas = $this->prioritas($analisis, 'E.90', 'Uji ambang');

        $this->expectException(InvalidArgumentException::class);
        $this->analyzer->telusuri($prioritas);
    }
}

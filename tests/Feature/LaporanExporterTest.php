<?php

namespace Tests\Feature;

use App\Enums\Keyakinan;
use App\Http\Livewire\Dinas\Prioritas;
use App\Models\Analisis;
use App\Models\AnalisisAkar;
use App\Models\AnalisisPrioritas;
use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\RencanaAksi;
use App\Models\Wilayah;
use App\Services\Akar\Output\LaporanExporter;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class LaporanExporterTest extends TestCase
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

        $this->impor = ImporBerkas::factory()->create([
            'jenis' => 'daerah', 'tahun_edisi' => 2025, 'status' => 'selesai',
        ]);
        $this->wilayah = Wilayah::factory()->create([
            'level' => 'kabkota', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kabupaten Bangkalan',
        ]);
        $this->analisis = Analisis::factory()->create([
            'wilayah_id' => $this->wilayah->id,
            'tahun' => 2025,
            'jenis_satuan' => self::JENIS,
            'status_satuan' => self::STATUS,
        ]);

        $this->prioritas('A.1', 'Kemampuan literasi', 87.5, 1, withAkar: true);
        $this->prioritas('E.9', 'Indikator belum dipetakan', 60.0, 2, withAkar: false);
    }

    private function prioritas(string $nomor, string $nama, float $skor, int $peringkat, bool $withAkar): AnalisisPrioritas
    {
        $indikator = Indikator::factory()->nomor($nomor)->create([
            'nama' => $nama,
            'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH,
            'tersedia_kabkota' => true,
        ]);

        Capaian::factory()->create([
            'impor_id' => $this->impor->id,
            'wilayah_id' => $this->wilayah->id,
            'indikator_id' => $indikator->id,
            'tahun' => 2025,
            'jenis_satuan' => self::JENIS,
            'status_satuan' => self::STATUS,
            'label_capaian' => 'Kurang',
            'perubahan_nilai' => 'Turun',
        ]);

        $p = AnalisisPrioritas::factory()->create([
            'analisis_id' => $this->analisis->id,
            'indikator_id' => $indikator->id,
            'skor' => $skor,
            'peringkat' => $peringkat,
            'komponen_skor' => [
                ['kode' => 'label', 'nama' => 'Label capaian', 'bobot_maks' => 40, 'nilai_0_1' => 1.0, 'kontribusi' => 40],
                ['kode' => 'perubahan', 'nama' => 'Arah perubahan', 'bobot_maks' => 25, 'nilai_0_1' => 1.0, 'kontribusi' => 25],
                ['kode' => 'posisi', 'nama' => 'Posisi relatif', 'bobot_maks' => 20, 'nilai_0_1' => 0.6, 'kontribusi' => 12],
                ['kode' => 'turunan', 'nama' => 'Dampak turunan', 'bobot_maks' => 15, 'nilai_0_1' => 0.7, 'kontribusi' => 10.5],
            ],
        ]);

        if ($withAkar) {
            AnalisisAkar::factory()->keyakinan(Keyakinan::Kuat)->create([
                'analisis_prioritas_id' => $p->id,
                'kode_akar' => 'budaya_refleksi',
                'label' => 'Budaya refleksi dan perbaikan pembelajaran belum berjalan',
                'bukti' => [['nomor' => 'D.2', 'nama' => 'Refleksi guru', 'label' => 'Kurang']],
            ]);
        }

        return $p;
    }

    private function exporter(): LaporanExporter
    {
        return app(LaporanExporter::class);
    }

    public function test_pdf_menghasilkan_dokumen_pdf_yang_memuat_isi_laporan(): void
    {
        $keluaran = $this->exporter()->pdf($this->analisis)->output();

        $this->assertStringStartsWith('%PDF-', $keluaran);
        $this->assertGreaterThan(1000, strlen($keluaran));
    }

    public function test_pdf_memuat_wilayah_dan_indikator_lewat_teks(): void
    {
        // Render blade laporan langsung agar isi tekstualnya dapat diperiksa
        // tanpa membongkar berkas PDF biner.
        $exporter = $this->exporter();
        $ref = new \ReflectionMethod($exporter, 'data');
        $ref->setAccessible(true);
        $data = $ref->invoke($exporter, $this->analisis);
        $html = view('laporan.pdf', $data)->render();

        $this->assertStringContainsString('Kabupaten Bangkalan', $html);
        $this->assertStringContainsString('A.1 Kemampuan literasi', $html);
        $this->assertStringContainsString('Budaya refleksi', $html);
        $this->assertStringContainsString('Rekomendasi akar masalah belum tersedia', $html); // E.9
        // Sumber data wajib tercantum di kaki dokumen (DESIGN.md 8).
        $this->assertStringContainsString('Data Rapor Pendidikan Indonesia', $html);
        $this->assertStringContainsString('Kementerian Pendidikan Dasar dan Menengah', $html);
    }

    public function test_excel_menghasilkan_satu_baris_per_indikator_prioritas(): void
    {
        $baris = $this->exporter()->excel($this->analisis)->array();

        $this->assertCount(2, $baris);
        // urut peringkat: A.1 dulu
        $this->assertSame('A.1', $baris[0][1]);
        $this->assertSame('Kemampuan literasi', $baris[0][2]);
        $this->assertEqualsWithDelta(87.5, $baris[0][5], 0.01);
        // kolom komponen skor terisi (skor dapat ditelusuri)
        $this->assertEqualsWithDelta(40.0, $baris[0][6], 0.01);
        $this->assertStringContainsString('Budaya refleksi', $baris[0][10]);
        $this->assertSame('Bukti kuat', $baris[0][11]);
    }

    public function test_nama_berkas_memuat_wilayah_jenjang_tahun(): void
    {
        $nama = $this->exporter()->namaBerkas($this->analisis, 'pdf');

        $this->assertSame('AKAR Kabupaten Bangkalan SMP Umum 2025.pdf', $nama);
    }

    public function test_tombol_unduh_pdf_pada_halaman_prioritas_memicu_unduhan(): void
    {
        Livewire::test(Prioritas::class)
            ->set('tahun', 2025)
            ->set('provinsi', 'Jawa Timur')
            ->set('wilayahId', $this->wilayah->id)
            ->set('jenisSatuan', self::JENIS)
            ->set('statusSatuan', self::STATUS)
            ->call('unduhPdf')
            ->assertFileDownloaded('AKAR Kabupaten Bangkalan SMP Umum 2025.pdf');
    }

    public function test_tombol_unduh_excel_memakai_maatwebsite_excel(): void
    {
        Excel::fake();

        Livewire::test(Prioritas::class)
            ->set('tahun', 2025)
            ->set('provinsi', 'Jawa Timur')
            ->set('wilayahId', $this->wilayah->id)
            ->set('jenisSatuan', self::JENIS)
            ->set('statusSatuan', self::STATUS)
            ->call('unduhExcel');

        Excel::assertDownloaded('AKAR Kabupaten Bangkalan SMP Umum 2025.xlsx');
    }

    public function test_rencana_ikut_di_pdf_bila_ada(): void
    {
        $rencana = RencanaAksi::factory()->create(['analisis_id' => $this->analisis->id, 'judul' => 'Rencana Uji']);
        $rencana->item()->create([
            'masalah' => 'Literasi Kurang',
            'akar_masalah' => 'Budaya refleksi lemah',
            'kegiatan' => 'Lokakarya refleksi',
            'urutan' => 0,
        ]);

        $exporter = $this->exporter();
        $ref = new \ReflectionMethod($exporter, 'data');
        $ref->setAccessible(true);
        $html = view('laporan.pdf', $ref->invoke($exporter, $this->analisis))->render();

        $this->assertStringContainsString('Rencana Uji', $html);
        $this->assertStringContainsString('Lokakarya refleksi', $html);
    }
}

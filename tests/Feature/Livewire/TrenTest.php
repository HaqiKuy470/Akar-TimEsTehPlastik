<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Dinas\Tren;
use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TrenTest extends TestCase
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

    private function capaian(string $nomor, string $nama, int $tahun, string $label): void
    {
        $indikator = Indikator::query()->firstWhere('nomor', $nomor)
            ?? Indikator::factory()->nomor($nomor)->create(['nama' => $nama, 'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH]);
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

    private function komponen()
    {
        return Livewire::test(Tren::class)
            ->set('provinsi', 'Jawa Timur')
            ->set('wilayahId', $this->wilayah->id)
            ->set('jenisSatuan', self::JENIS)
            ->set('statusSatuan', self::STATUS);
    }

    public function test_render_awal_tanpa_pilihan(): void
    {
        Livewire::test(Tren::class)
            ->assertOk()
            ->assertSee('Tren lintas tahun');
    }

    public function test_satu_edisi_menampilkan_pesan_perlu_dua_edisi(): void
    {
        $this->capaian('A.1', 'Literasi', 2025, 'Kurang');

        $this->komponen()->assertSee('memerlukan minimal dua edisi');
    }

    public function test_menampilkan_indikator_memburuk_berturut(): void
    {
        $this->capaian('A.1', 'Kemampuan literasi', 2023, 'Baik');
        $this->capaian('A.1', 'Kemampuan literasi', 2024, 'Sedang');
        $this->capaian('A.1', 'Kemampuan literasi', 2025, 'Kurang');

        $this->komponen()
            ->assertSee('Memburuk dua tahun berturut-turut')
            ->assertSee('Kemampuan literasi');
    }

    public function test_mengganti_provinsi_mengosongkan_kabupaten_kota(): void
    {
        Livewire::test(Tren::class)
            ->set('wilayahId', $this->wilayah->id)
            ->set('provinsi', 'Bali')
            ->assertSet('wilayahId', null);
    }
}

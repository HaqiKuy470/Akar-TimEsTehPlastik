<?php

namespace Tests\Feature\Livewire;

use App\Enums\Keyakinan;
use App\Http\Livewire\Dinas\RencanaTindakLanjut;
use App\Models\Analisis;
use App\Models\AnalisisAkar;
use App\Models\AnalisisPrioritas;
use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RencanaTindakLanjutTest extends TestCase
{
    use RefreshDatabase;

    private Analisis $analisis;

    protected function setUp(): void
    {
        parent::setUp();

        $impor = ImporBerkas::factory()->create(['jenis' => 'daerah', 'tahun_edisi' => 2025, 'status' => 'selesai']);
        $wilayah = Wilayah::factory()->create(['level' => 'kabkota', 'provinsi' => 'Jawa Timur', 'kabupaten_kota' => 'Kabupaten Bangkalan']);
        $this->analisis = Analisis::factory()->create([
            'wilayah_id' => $wilayah->id,
            'tahun' => 2025,
            'jenis_satuan' => 'SMP Umum',
            'status_satuan' => 'Semua (Negeri dan Swasta)',
        ]);

        $indikator = Indikator::factory()->nomor('A.1')->create([
            'nama' => 'Kemampuan literasi',
            'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH,
        ]);
        Capaian::factory()->create([
            'impor_id' => $impor->id,
            'wilayah_id' => $wilayah->id,
            'indikator_id' => $indikator->id,
            'tahun' => 2025,
            'jenis_satuan' => 'SMP Umum',
            'status_satuan' => 'Semua (Negeri dan Swasta)',
            'label_capaian' => 'Kurang',
            'perubahan_nilai' => 'Turun',
        ]);
        $prioritas = AnalisisPrioritas::factory()->create([
            'analisis_id' => $this->analisis->id,
            'indikator_id' => $indikator->id,
        ]);
        AnalisisAkar::factory()->keyakinan(Keyakinan::Kuat)->create([
            'analisis_prioritas_id' => $prioritas->id,
            'kode_akar' => 'kualitas_pembelajaran',
            'label' => 'Kualitas praktik pembelajaran di kelas belum optimal',
        ]);
    }

    public function test_komponen_dapat_dirender(): void
    {
        Livewire::test(RencanaTindakLanjut::class)->assertOk()->assertSee('Susun draf pembenahan dari hasil analisis akar masalah');
    }

    public function test_susun_draf_menghasilkan_item_yang_dapat_disunting(): void
    {
        Livewire::test(RencanaTindakLanjut::class)
            ->set('analisisId', $this->analisis->id)
            ->call('susunDraf')
            ->assertSee('Kemampuan literasi berlabel Kurang')
            ->assertCount('item', 3);
    }

    public function test_menyunting_sel_lalu_simpan_menulis_ke_basis_data(): void
    {
        Livewire::test(RencanaTindakLanjut::class)
            ->set('analisisId', $this->analisis->id)
            ->call('susunDraf')
            ->set('item.0.penanggung_jawab', 'Bidang Pembinaan SMP')
            ->call('simpan')
            ->assertSet('tersimpan', true);

        $this->assertDatabaseHas('rencana_aksi_item', ['penanggung_jawab' => 'Bidang Pembinaan SMP']);
    }

    public function test_tambah_dan_hapus_baris(): void
    {
        $komponen = Livewire::test(RencanaTindakLanjut::class)
            ->set('analisisId', $this->analisis->id)
            ->call('susunDraf')
            ->call('tambahBaris')
            ->set('item.3.masalah', 'Butir tambahan manual')
            ->assertCount('item', 4)
            ->call('hapusBaris', 0)
            ->assertCount('item', 3);

        $komponen->call('simpan');
        $this->assertSame(3, $this->analisis->rencanaAksi()->first()->item()->count());
    }

    public function test_draf_tersimpan_dimuat_kembali_saat_analisis_dipilih_ulang(): void
    {
        Livewire::test(RencanaTindakLanjut::class)
            ->set('analisisId', $this->analisis->id)
            ->call('susunDraf')
            ->call('simpan');

        Livewire::test(RencanaTindakLanjut::class)
            ->set('analisisId', $this->analisis->id)
            ->assertCount('item', 3)
            ->assertSee('Susun ulang draf');
    }
}

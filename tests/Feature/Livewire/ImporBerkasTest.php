<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Dinas\ImporBerkas;
use App\Models\ImporBerkas as ImporBerkasModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ImporBerkasTest extends TestCase
{
    use RefreshDatabase;

    // Uji HTTP lewat route('dinas.impor') ditambahkan setelah route terdaftar
    // (milik penggabung). Di sini komponen diuji langsung via Livewire::test.

    public function test_keadaan_kosong_saat_belum_ada_impor(): void
    {
        Livewire::test(ImporBerkas::class)
            ->assertSee('Belum ada berkas yang diimpor')
            ->assertSee('Impor berkas')
            ->assertSet('adaYangDiproses', false);
    }

    public function test_menampilkan_riwayat_dengan_status(): void
    {
        ImporBerkasModel::factory()->create([
            'nama_berkas' => '2025_rapor.xlsx',
            'jenis' => 'daerah',
            'tahun_edisi' => 2025,
            'status' => 'selesai',
            'jumlah_baris' => 951,
        ]);
        ImporBerkasModel::factory()->create([
            'nama_berkas' => 'gagal.xlsx',
            'status' => 'gagal',
            'catatan_galat' => 'Jawa Timur: struktur tidak dikenali',
        ]);

        Livewire::test(ImporBerkas::class)
            ->assertSee('2025_rapor.xlsx')
            ->assertSee('Selesai')
            ->assertSee('Gagal')
            ->assertSee('struktur tidak dikenali')
            ->assertSet('adaYangDiproses', false);
    }

    public function test_menandai_ada_yang_diproses_untuk_polling(): void
    {
        ImporBerkasModel::factory()->create(['status' => 'proses']);

        Livewire::test(ImporBerkas::class)
            ->assertSet('adaYangDiproses', true)
            ->assertSeeHtml('wire:poll.5s');
    }
}

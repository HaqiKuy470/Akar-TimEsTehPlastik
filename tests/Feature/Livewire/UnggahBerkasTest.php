<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Sekolah\UnggahBerkas;
use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\Akar\PemetaanJenisLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class UnggahBerkasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            ['A.1', 'Kemampuan literasi'],
            ['A.1.1', 'Kompetensi membaca teks informasi'],
            ['D.4', 'Iklim keamanan satuan pendidikan'],
        ] as [$nomor, $nama]) {
            Indikator::factory()->nomor($nomor)->create([
                'nama' => $nama,
                'jenis_layanan' => PemetaanJenisLayanan::DASAR_MENENGAH,
            ]);
        }

        $this->actingAs(User::factory()->create());
    }

    private function berkas(string $namaFixture, string $namaUnggahan = 'rapor.xlsx'): UploadedFile
    {
        // Livewire menguji unggahan lewat berkas palsu (Illuminate\Http\Testing\File);
        // isinya diambil dari fixture xlsx sungguhan agar parser tetap teruji.
        return UploadedFile::fake()->createWithContent(
            $namaUnggahan,
            file_get_contents(__DIR__.'/../../Fixtures/'.$namaFixture),
        );
    }

    public function test_komponen_dirender(): void
    {
        Livewire::test(UnggahBerkas::class)
            ->assertOk()
            ->assertSee('Unggah Rapor Pendidikan sekolah')
            ->assertSee('belum diuji tim dengan berkas asli');
    }

    public function test_berkas_satuan_diproses_dan_menghasilkan_analisis(): void
    {
        Livewire::test(UnggahBerkas::class)
            ->set('berkas', $this->berkas('berkas_sekolah_mini.xlsx'))
            ->call('proses')
            ->assertSet('galat', null);

        $impor = ImporBerkas::where('jenis', 'satuan')->first();
        $this->assertNotNull($impor);
        $this->assertSame('selesai', $impor->status); // antrean sync di pengujian
        $this->assertTrue(Wilayah::where('level', 'satuan')->exists());
        $this->assertGreaterThan(0, Capaian::count());
    }

    public function test_berkas_daerah_ditolak_dengan_pengarahan(): void
    {
        Livewire::test(UnggahBerkas::class)
            ->set('berkas', $this->berkas('sheet_provinsi_mini.xlsx'))
            ->call('proses')
            ->assertSet('imporId', null);

        $komponen = Livewire::test(UnggahBerkas::class)
            ->set('berkas', $this->berkas('sheet_provinsi_mini.xlsx'))
            ->call('proses');
        $this->assertStringContainsString('tingkat daerah', $komponen->get('galat'));
        $this->assertSame(0, ImporBerkas::count());
    }

    public function test_berkas_tak_dikenal_ditolak_dengan_pesan_format(): void
    {
        $komponen = Livewire::test(UnggahBerkas::class)
            ->set('berkas', $this->berkas('berkas_tak_dikenal.xlsx'))
            ->call('proses');

        $this->assertStringContainsString('tidak dapat dikenali', $komponen->get('galat'));
        $this->assertSame(0, ImporBerkas::count());
    }

    public function test_menolak_berkas_bukan_xlsx(): void
    {
        Livewire::test(UnggahBerkas::class)
            ->set('berkas', UploadedFile::fake()->create('rapor.pdf', 100, 'application/pdf'))
            ->call('proses')
            ->assertHasErrors('berkas');
    }

    public function test_unggah_ulang_berkas_sama_tidak_menggandakan(): void
    {
        Livewire::test(UnggahBerkas::class)
            ->set('berkas', $this->berkas('berkas_sekolah_mini.xlsx'))
            ->call('proses');
        $jumlah = Capaian::count();

        Livewire::test(UnggahBerkas::class)
            ->set('berkas', $this->berkas('berkas_sekolah_mini.xlsx'))
            ->call('proses');

        $this->assertSame(1, ImporBerkas::count());
        $this->assertSame($jumlah, Capaian::count());
    }
}

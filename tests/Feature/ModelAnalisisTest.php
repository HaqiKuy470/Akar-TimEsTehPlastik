<?php

namespace Tests\Feature;

use App\Enums\Keyakinan;
use App\Models\Analisis;
use App\Models\AnalisisAkar;
use App\Models\AnalisisPrioritas;
use App\Models\Indikator;
use App\Models\RencanaAksi;
use App\Models\RencanaAksiItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelAnalisisTest extends TestCase
{
    use RefreshDatabase;

    public function test_analisis_punya_prioritas_dan_rencana_aksi(): void
    {
        $analisis = Analisis::factory()
            ->has(AnalisisPrioritas::factory()->count(3), 'prioritas')
            ->has(RencanaAksi::factory()->count(2), 'rencanaAksi')
            ->create();

        $this->assertCount(3, $analisis->prioritas);
        $this->assertCount(2, $analisis->rencanaAksi);
        $this->assertInstanceOf(Analisis::class, $analisis->prioritas->first()->analisis);
    }

    public function test_analisis_belongs_to_pembuat(): void
    {
        $user = User::factory()->create();
        $analisis = Analisis::factory()->for($user, 'pembuat')->create();

        $this->assertTrue($analisis->pembuat->is($user));
    }

    public function test_bobot_dipakai_disimpan_sebagai_array(): void
    {
        $analisis = Analisis::factory()->create([
            'bobot_dipakai' => ['bobot_komponen' => ['label' => 40]],
        ]);

        $this->assertSame(40, $analisis->fresh()->bobot_dipakai['bobot_komponen']['label']);
    }

    public function test_prioritas_belongs_to_indikator_dan_komponen_skor_array(): void
    {
        $prioritas = AnalisisPrioritas::factory()->create();

        $this->assertInstanceOf(Indikator::class, $prioritas->indikator);
        $this->assertNotEmpty($prioritas->indikator->nomor);
        $this->assertIsArray($prioritas->fresh()->komponen_skor);
        $this->assertArrayHasKey('label', $prioritas->fresh()->komponen_skor);
    }

    public function test_prioritas_punya_banyak_akar_masalah(): void
    {
        $prioritas = AnalisisPrioritas::factory()
            ->has(AnalisisAkar::factory()->count(2), 'akar')
            ->create();

        $this->assertCount(2, $prioritas->akar);
        $this->assertTrue($prioritas->akar->first()->prioritas->is($prioritas));
    }

    public function test_akar_masalah_cast_keyakinan_dan_bukti(): void
    {
        $akar = AnalisisAkar::factory()
            ->keyakinan(Keyakinan::Kuat)
            ->create();

        $segar = $akar->fresh();
        $this->assertSame(Keyakinan::Kuat, $segar->keyakinan);
        $this->assertIsArray($segar->bukti);
        $this->assertArrayHasKey('nomor', $segar->bukti[0]);
    }

    public function test_rencana_aksi_item_terurut_menurut_urutan(): void
    {
        $rencana = RencanaAksi::factory()->create();
        RencanaAksiItem::factory()->for($rencana, 'rencanaAksi')->create(['urutan' => 3]);
        RencanaAksiItem::factory()->for($rencana, 'rencanaAksi')->create(['urutan' => 1]);
        RencanaAksiItem::factory()->for($rencana, 'rencanaAksi')->create(['urutan' => 2]);

        $this->assertSame([1, 2, 3], $rencana->item->pluck('urutan')->all());
        $this->assertTrue($rencana->item->first()->rencanaAksi->is($rencana));
    }

    public function test_hapus_analisis_menghapus_turunannya(): void
    {
        $analisis = Analisis::factory()
            ->has(
                AnalisisPrioritas::factory()->has(AnalisisAkar::factory(), 'akar'),
                'prioritas'
            )
            ->has(RencanaAksi::factory()->has(RencanaAksiItem::factory(), 'item'), 'rencanaAksi')
            ->create();

        $analisis->delete();

        $this->assertSame(0, AnalisisPrioritas::count());
        $this->assertSame(0, AnalisisAkar::count());
        $this->assertSame(0, RencanaAksi::count());
        $this->assertSame(0, RencanaAksiItem::count());
    }
}

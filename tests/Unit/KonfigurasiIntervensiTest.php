<?php

namespace Tests\Unit;

use Tests\TestCase;

class KonfigurasiIntervensiTest extends TestCase
{
    private const AMBANG_DIKENALI = [
        'minimal_satu_kurang',
        'minimal_dua_kurang',
        'mayoritas_bermasalah',
    ];

    private const KUNCI_KEGIATAN = [
        'nama',
        'deskripsi',
        'penanggung_jawab',
        'indikator_keberhasilan',
        'perkiraan_waktu',
    ];

    public function test_cakupan_mvp_antara_15_dan_20_indikator(): void
    {
        $jumlah = count(config('intervensi'));

        $this->assertGreaterThanOrEqual(15, $jumlah);
        $this->assertLessThanOrEqual(20, $jumlah);
    }

    public function test_setiap_entri_intervensi_berbentuk_benar(): void
    {
        foreach (config('intervensi') as $nomor => $entri) {
            $this->assertIsArray($entri, "Entri {$nomor} bukan array.");
            $this->assertArrayHasKey('nama', $entri, "Entri {$nomor} tidak punya 'nama'.");
            $this->assertNotEmpty($entri['nama']);
            $this->assertArrayHasKey('kandidat_akar', $entri, "Entri {$nomor} tidak punya 'kandidat_akar'.");
            $this->assertIsArray($entri['kandidat_akar']);
            $this->assertNotEmpty($entri['kandidat_akar'], "Entri {$nomor} tidak memiliki kandidat akar.");
        }
    }

    public function test_setiap_kandidat_akar_berbentuk_benar(): void
    {
        foreach (config('intervensi') as $nomor => $entri) {
            foreach ($entri['kandidat_akar'] as $i => $kandidat) {
                $jejak = "{$nomor} kandidat #{$i}";

                foreach (['kode', 'label', 'periksa', 'ambang', 'kegiatan'] as $kunci) {
                    $this->assertArrayHasKey($kunci, $kandidat, "{$jejak} tidak punya '{$kunci}'.");
                }

                $this->assertNotEmpty($kandidat['kode'], "{$jejak}: kode kosong.");
                $this->assertNotEmpty($kandidat['label'], "{$jejak}: label kosong.");

                $this->assertIsArray($kandidat['periksa'], "{$jejak}: 'periksa' bukan array.");
                $this->assertNotEmpty($kandidat['periksa'], "{$jejak}: 'periksa' kosong.");
                foreach ($kandidat['periksa'] as $p) {
                    $this->assertMatchesRegularExpression(
                        '/^[A-E]\.\d+(\.\d+)*$/',
                        $p,
                        "{$jejak}: nomor indikator '{$p}' tidak valid."
                    );
                }

                $this->assertContains(
                    $kandidat['ambang'],
                    self::AMBANG_DIKENALI,
                    "{$jejak}: ambang '{$kandidat['ambang']}' tidak dikenali."
                );

                $this->assertIsArray($kandidat['kegiatan'], "{$jejak}: 'kegiatan' bukan array.");
                $this->assertNotEmpty($kandidat['kegiatan'], "{$jejak}: 'kegiatan' kosong.");
            }
        }
    }

    public function test_kode_kandidat_unik_dalam_satu_indikator(): void
    {
        foreach (config('intervensi') as $nomor => $entri) {
            $kode = array_column($entri['kandidat_akar'], 'kode');
            $this->assertSame(
                count($kode),
                count(array_unique($kode)),
                "Kode kandidat akar pada {$nomor} tidak unik."
            );
        }
    }

    public function test_semua_kegiatan_yang_dirujuk_ada_di_katalog(): void
    {
        $katalog = array_keys(config('kegiatan'));

        foreach (config('intervensi') as $nomor => $entri) {
            foreach ($entri['kandidat_akar'] as $i => $kandidat) {
                foreach ($kandidat['kegiatan'] as $kodeKegiatan) {
                    $this->assertContains(
                        $kodeKegiatan,
                        $katalog,
                        "{$nomor} kandidat #{$i} merujuk kegiatan '{$kodeKegiatan}' yang tidak ada di config/kegiatan.php."
                    );
                }
            }
        }
    }

    public function test_setiap_entri_kegiatan_punya_lima_kunci_wajib(): void
    {
        foreach (config('kegiatan') as $kode => $kegiatan) {
            $this->assertIsArray($kegiatan, "Kegiatan '{$kode}' bukan array.");
            foreach (self::KUNCI_KEGIATAN as $kunci) {
                $this->assertArrayHasKey($kunci, $kegiatan, "Kegiatan '{$kode}' tidak punya '{$kunci}'.");
                $this->assertNotEmpty($kegiatan[$kunci], "Kegiatan '{$kode}' kunci '{$kunci}' kosong.");
            }
        }
    }

    public function test_tidak_ada_kegiatan_yatim_di_katalog(): void
    {
        $dirujuk = [];
        foreach (config('intervensi') as $entri) {
            foreach ($entri['kandidat_akar'] as $kandidat) {
                foreach ($kandidat['kegiatan'] as $kode) {
                    $dirujuk[$kode] = true;
                }
            }
        }

        $yatim = array_diff(array_keys(config('kegiatan')), array_keys($dirujuk));

        $this->assertSame(
            [],
            array_values($yatim),
            'Ada kegiatan di katalog yang tidak dirujuk intervensi mana pun: '.implode(', ', $yatim)
        );
    }
}

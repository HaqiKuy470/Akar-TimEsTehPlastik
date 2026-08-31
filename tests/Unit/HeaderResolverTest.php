<?php

namespace Tests\Unit;

use App\Services\Akar\Parsers\HeaderResolver;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class HeaderResolverTest extends TestCase
{
    private HeaderResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new HeaderResolver;
    }

    /**
     * Header minimal yang wajar: 4 kolom dimensi + dua indikator (A.1 dan A.1.1),
     * masing-masing sepasang kolom label/perubahan. Baris 6 dan 7 memakai sel
     * ter-merge sehingga hanya kolom pertama tiap kelompok yang terisi.
     *
     * @return array{0: list<?string>, 1: list<?string>, 2: list<?string>}
     */
    private function headerContoh(): array
    {
        $baris6 = [null, null, null, null, 'A.1', null, null, null];
        $baris7 = [null, null, null, null, 'A.1 Kemampuan literasi', null, 'A.1.1 Kompetensi membaca teks informasi', null];
        $baris8 = [
            'Provinsi', 'Kabupaten/Kota', "Jenis Satuan Pendidikan\n", "Status Satuan Pendidikan\n",
            'Label Capaian 2025', 'Perubahan Nilai Capaian dari Tahun Lalu',
            'Label Capaian 2025', 'Perubahan Nilai Capaian dari Tahun Lalu',
        ];

        return [$baris6, $baris7, $baris8];
    }

    public function test_kolom_1_sampai_4_dikenali_sebagai_dimensi(): void
    {
        [$b6, $b7, $b8] = $this->headerContoh();
        $peta = $this->resolver->resolve($b6, $b7, $b8);

        $this->assertSame('dimensi', $peta[1]['jenis']);
        $this->assertSame('provinsi', $peta[1]['dimensi']);
        $this->assertSame('kabupaten_kota', $peta[2]['dimensi']);
        $this->assertSame('jenis_satuan', $peta[3]['dimensi']);
        $this->assertSame('status_satuan', $peta[4]['dimensi']);
    }

    public function test_kolom_indikator_diberi_nomor_nama_dan_peran(): void
    {
        [$b6, $b7, $b8] = $this->headerContoh();
        $peta = $this->resolver->resolve($b6, $b7, $b8);

        $this->assertSame('indikator', $peta[5]['jenis']);
        $this->assertSame('A.1', $peta[5]['nomor']);
        $this->assertSame('Kemampuan literasi', $peta[5]['nama']);
        $this->assertSame('label', $peta[5]['peran']);

        // Kolom 6 adalah sel bekas merge di baris 7 -> forward-fill membuatnya
        // tetap indikator A.1 dengan peran perubahan.
        $this->assertSame('A.1', $peta[6]['nomor']);
        $this->assertSame('perubahan', $peta[6]['peran']);

        $this->assertSame('A.1.1', $peta[7]['nomor']);
        $this->assertSame('Kompetensi membaca teks informasi', $peta[7]['nama']);
    }

    public function test_induk_diambil_dari_baris_6_bila_berbeda_dari_nomor_sendiri(): void
    {
        [$b6, $b7, $b8] = $this->headerContoh();
        $peta = $this->resolver->resolve($b6, $b7, $b8);

        // A.1 induknya sama dengan dirinya -> null
        $this->assertNull($peta[5]['induk']);
        // A.1.1 induknya A.1 (dari baris 6 yang di-forward-fill)
        $this->assertSame('A.1', $peta[7]['induk']);
    }

    public function test_nomor_sama_dengan_nama_berbeda_tetap_dipetakan_terpisah(): void
    {
        $b6 = [null, null, null, null, 'D.2', null, null, null];
        $b7 = [
            null, null, null, null,
            'D.2 Refleksi dan perbaikan pembelajaran oleh guru', null,
            'D.2 Proses belajar yang sesuai bagi anak usia dini', null,
        ];
        $b8 = [
            'Provinsi', 'Kabupaten/Kota', 'Jenis Satuan Pendidikan', 'Status Satuan Pendidikan',
            'Label Capaian 2025', 'Perubahan Nilai Capaian dari Tahun Lalu',
            'Label Capaian 2025', 'Perubahan Nilai Capaian dari Tahun Lalu',
        ];

        $peta = $this->resolver->resolve($b6, $b7, $b8);

        $this->assertSame('D.2', $peta[5]['nomor']);
        $this->assertSame('Refleksi dan perbaikan pembelajaran oleh guru', $peta[5]['nama']);
        $this->assertSame('D.2', $peta[7]['nomor']);
        $this->assertSame('Proses belajar yang sesuai bagi anak usia dini', $peta[7]['nama']);
    }

    public function test_kolom_sisa_tanpa_judul_diabaikan_walau_nama_terisi_akibat_forward_fill(): void
    {
        [$b6, $b7, $b8] = $this->headerContoh();
        // Tambah dua kolom sisa: baris 7 kosong (akan ter-forward-fill), baris 8 kosong.
        $b6[] = null;
        $b6[] = null;
        $b7[] = null;
        $b7[] = null;
        $b8[] = null;
        $b8[] = null;

        $peta = $this->resolver->resolve($b6, $b7, $b8);

        $this->assertArrayNotHasKey(9, $peta);
        $this->assertArrayNotHasKey(10, $peta);
        $this->assertCount(8, $peta); // 4 dimensi + 2 indikator x 2 kolom
    }

    public function test_baris_7_tanpa_pola_nomor_melempar_pengecualian_dengan_indeks_kolom(): void
    {
        [$b6, $b7, $b8] = $this->headerContoh();
        $b7[6] = 'Judul indikator tanpa nomor';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/[Kk]olom 7/');
        $this->resolver->resolve($b6, $b7, $b8);
    }

    public function test_kolom_dengan_judul_tetapi_tanpa_nama_indikator_melempar_pengecualian(): void
    {
        $b6 = [null, null, null, null, null, null];
        $b7 = [null, null, null, null, null, null];
        $b8 = [
            'Provinsi', 'Kabupaten/Kota', 'Jenis Satuan Pendidikan', 'Status Satuan Pendidikan',
            'Label Capaian 2025', 'Perubahan Nilai Capaian dari Tahun Lalu',
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/[Kk]olom 5/');
        $this->resolver->resolve($b6, $b7, $b8);
    }

    public function test_peran_kolom_tak_dikenali_melempar_pengecualian(): void
    {
        [$b6, $b7, $b8] = $this->headerContoh();
        $b8[5] = 'Kolom aneh';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/[Kk]olom 6/');
        $this->resolver->resolve($b6, $b7, $b8);
    }

    public function test_header_tanpa_indikator_sama_sekali_melempar_pengecualian(): void
    {
        $b6 = [null, null, null, null, null, null];
        $b7 = [null, null, null, null, null, null];
        $b8 = ['Provinsi', 'Kabupaten/Kota', 'Jenis Satuan Pendidikan', 'Status Satuan Pendidikan', null, null];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/indikator/i');
        $this->resolver->resolve($b6, $b7, $b8);
    }
}

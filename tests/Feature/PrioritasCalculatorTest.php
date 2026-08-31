<?php

namespace Tests\Feature;

use App\Services\Akar\Analysis\PrioritasCalculator;
use InvalidArgumentException;
use Tests\TestCase;

class PrioritasCalculatorTest extends TestCase
{
    private function kalkulator(): PrioritasCalculator
    {
        // Bobot eksplisit agar uji tidak bergantung pada isi config yang bisa berubah.
        return new PrioritasCalculator([
            'bobot_komponen' => ['label' => 40, 'perubahan' => 25, 'posisi' => 20, 'turunan' => 15],
            'nilai_label' => ['Kurang' => 1.0, 'Sedang' => 0.5, 'Baik' => 0.0],
            'nilai_perubahan' => ['Turun' => 1.0, 'Tidak berubah' => 0.5, 'Naik' => 0.0, 'Tidak Tersedia' => 0.0],
        ]);
    }

    public function test_kondisi_terparah_menghasilkan_skor_maksimum(): void
    {
        $hasil = $this->kalkulator()->hitung([
            'label' => 'Kurang',
            'perubahan' => 'Turun',
            'bobot_posisi' => 1.0,
            'bobot_turunan' => 1.0,
        ]);

        $this->assertSame(100.0, $hasil['skor']);
    }

    public function test_kondisi_terbaik_menghasilkan_skor_nol(): void
    {
        $hasil = $this->kalkulator()->hitung([
            'label' => 'Baik',
            'perubahan' => 'Naik',
            'bobot_posisi' => 0.0,
            'bobot_turunan' => 0.0,
        ]);

        $this->assertSame(0.0, $hasil['skor']);
    }

    public function test_nilai_tengah_dijumlahkan_dari_tiap_komponen(): void
    {
        // 40*0.5 + 25*0.5 + 20*0.25 + 15*0.4 = 20 + 12.5 + 5 + 6 = 43.5
        $hasil = $this->kalkulator()->hitung([
            'label' => 'Sedang',
            'perubahan' => 'Tidak berubah',
            'bobot_posisi' => 0.25,
            'bobot_turunan' => 0.4,
        ]);

        $this->assertSame(43.5, $hasil['skor']);
    }

    public function test_setiap_skor_menyertakan_rincian_empat_komponen(): void
    {
        $hasil = $this->kalkulator()->hitung([
            'label' => 'Kurang',
            'perubahan' => 'Turun',
            'bobot_posisi' => 0.5,
            'bobot_turunan' => 0.2,
        ]);

        $this->assertCount(4, $hasil['komponen']);
        $this->assertSame(['label', 'perubahan', 'posisi', 'turunan'], array_column($hasil['komponen'], 'kode'));

        $label = $hasil['komponen'][0];
        $this->assertSame(40, $label['bobot_maks']);
        $this->assertSame(1.0, $label['nilai_0_1']);
        $this->assertSame(40.0, $label['kontribusi']);

        // Jumlah kontribusi harus sama dengan skor akhir (dapat ditelusuri).
        $this->assertSame($hasil['skor'], array_sum(array_column($hasil['komponen'], 'kontribusi')));
    }

    public function test_bobot_di_luar_rentang_dibatasi(): void
    {
        $hasil = $this->kalkulator()->hitung([
            'label' => 'Kurang',
            'perubahan' => 'Turun',
            'bobot_posisi' => 5.0,
            'bobot_turunan' => -2.0,
        ]);

        $this->assertSame(1.0, $hasil['komponen'][2]['nilai_0_1']);
        $this->assertSame(0.0, $hasil['komponen'][3]['nilai_0_1']);
    }

    public function test_label_tidak_tersedia_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->kalkulator()->hitung(['label' => 'Tidak Tersedia', 'perubahan' => 'Turun']);
    }
}

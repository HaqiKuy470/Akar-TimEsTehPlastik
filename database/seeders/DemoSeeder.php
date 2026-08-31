<?php

namespace Database\Seeders;

use App\Models\ImporBerkas;
use App\Services\Akar\Parsers\CapaianDaerahParser;
use App\Services\Akar\Parsers\MetadataIndikatorParser;
use Illuminate\Database\Seeder;
use Throwable;

/**
 * Menyiapkan basis data siap-demo: peran, akun demo, indikator dari berkas
 * Metadata, dan satu sheet provinsi contoh.
 *
 * Berjalan aman tanpa folder `dataset/` (langkah impor berkas dilewati dengan
 * pesan, tidak menggagalkan seeder). Dipakai oleh `php artisan akar:demo`.
 *
 * Ini BUKAN pengganti alur impor sebenarnya (lihat ARCHITECTURE.md bagian 4.1);
 * hanya jalan pintas agar penilai dan anggota tim bisa langsung mencoba.
 */
class DemoSeeder extends Seeder
{
    /** Sheet provinsi contoh yang diimpor untuk demo. */
    private const PROVINSI_CONTOH = 'Jawa Timur';

    public function run(): void
    {
        $this->call([
            PeranSeeder::class,
            AkunDemoSeeder::class,
        ]);

        $this->imporMetadata();
        $this->imporProvinsiContoh();
    }

    private function imporMetadata(): void
    {
        $path = $this->cariBerkas([
            'dataset/dataset-pendidikan/METADATA_INDIKATOR_RAPOR_PENDIDIKAN.csv',
            'dataset/METADATA_INDIKATOR_RAPOR_PENDIDIKAN.csv',
        ]);

        if ($path === null) {
            $this->command?->warn('  Berkas Metadata indikator tidak ditemukan, langkah impor indikator dilewati.');

            return;
        }

        $jumlah = app(MetadataIndikatorParser::class)->impor($path);
        $this->command?->info("  {$jumlah} indikator diimpor dari berkas Metadata.");
    }

    private function imporProvinsiContoh(): void
    {
        // Impor satu sheet provinsi asli memakan waktu puluhan detik dan berkas
        // 21 MB. Dilewati saat pengujian; alur impor punya test tersendiri.
        if (app()->runningUnitTests()) {
            return;
        }

        $path = $this->cariBerkas([
            'dataset/dataset-pendidikan/01_rapor_pendidikan_indonesia/2025_data-rapor-pendidikan-indonesia-2025.xlsx',
            'dataset/dataset-pendidikan/01_rapor_pendidikan_indonesia/2024_data-rapor-pendidikan-indonesia-2025-indonesia.xlsx',
        ]);

        if ($path === null) {
            $this->command?->warn('  Berkas Data Rapor Pendidikan tidak ditemukan, sheet provinsi contoh dilewati.');
            $this->command?->warn('  Isi data lewat: php artisan akar:impor <path-berkas>.xlsx');

            return;
        }

        $parser = app(CapaianDaerahParser::class);

        $sebelumnya = ini_get('memory_limit');
        ini_set('memory_limit', '1024M');

        try {
            $impor = ImporBerkas::firstOrCreate(
                ['hash_berkas' => hash_file('sha256', $path)],
                ['nama_berkas' => basename($path), 'jenis' => 'daerah', 'status' => 'proses'],
            );

            $tahun = $parser->deteksiTahunBerkas($path);
            $jumlah = $parser->imporSheet($path, self::PROVINSI_CONTOH, $impor, $tahun);

            $impor->update([
                'tahun_edisi' => $tahun,
                'status' => 'selesai',
                'jumlah_baris' => $jumlah,
                'diproses_pada' => now(),
            ]);

            $this->command?->info('  Sheet '.self::PROVINSI_CONTOH." edisi {$tahun}: {$jumlah} baris data.");
        } catch (Throwable $e) {
            $this->command?->warn('  Impor sheet provinsi contoh gagal: '.$e->getMessage());
        } finally {
            ini_set('memory_limit', (string) $sebelumnya);
        }
    }

    /**
     * @param  list<string>  $kandidat  path relatif terhadap base_path
     */
    private function cariBerkas(array $kandidat): ?string
    {
        foreach ($kandidat as $relatif) {
            $penuh = base_path($relatif);
            if (is_file($penuh)) {
                return $penuh;
            }
        }

        return null;
    }
}

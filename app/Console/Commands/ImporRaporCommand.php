<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ProsesImporBerkas;
use App\Services\Akar\Parsers\CapaianDaerahParser;
use App\Services\Akar\Parsers\MetadataIndikatorParser;
use Illuminate\Console\Command;
use Throwable;

/**
 * Titik masuk impor berkas Rapor Pendidikan di mesin lokal.
 *
 * Sesuai ARCHITECTURE.md bagian 4.1, parsing berkas daerah berukuran besar
 * dilakukan di lokal lewat perintah ini, bukan di server produksi. Hasilnya
 * dikirim ke produksi sebagai dump SQL.
 *
 *   .csv   -> berkas Metadata indikator  -> tabel indikator
 *   .xlsx  -> Data Rapor Pendidikan      -> tabel wilayah + capaian
 *
 * Impor Metadata harus dijalankan lebih dulu; sheet provinsi merujuk indikator
 * lewat nomor dan namanya.
 */
class ImporRaporCommand extends Command
{
    protected $signature = 'akar:impor
        {path : Path berkas Metadata (.csv) atau Data Rapor Pendidikan (.xlsx)}
        {--async : Untuk .xlsx, jalankan lewat antrean (satu job per sheet) alih-alih langsung}';

    protected $description = 'Impor berkas Rapor Pendidikan ke basis data lokal';

    public function handle(MetadataIndikatorParser $metadataParser, CapaianDaerahParser $capaianParser): int
    {
        $path = (string) $this->argument('path');

        if (! is_file($path)) {
            $this->error("Berkas tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $ekstensi = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        try {
            return match ($ekstensi) {
                'csv' => $this->imporMetadata($metadataParser, $path),
                'xlsx' => $this->imporDaerah($capaianParser, $path),
                default => $this->tolakEkstensi($ekstensi),
            };
        } catch (Throwable $e) {
            $this->error('Impor gagal: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function imporMetadata(MetadataIndikatorParser $parser, string $path): int
    {
        $this->info('Membaca berkas Metadata indikator...');
        $jumlah = $parser->impor($path);
        $this->info("Selesai. {$jumlah} indikator tersimpan di tabel indikator.");

        return self::SUCCESS;
    }

    private function imporDaerah(CapaianDaerahParser $parser, string $path): int
    {
        if ($this->option('async')) {
            ProsesImporBerkas::dispatch(realpath($path));
            $this->info('Berkas dijadwalkan. Jalankan antrean: php artisan queue:work --stop-when-empty');
            $this->line('Pantau status di halaman Impor berkas.');

            return self::SUCCESS;
        }

        // Memuat satu sheet provinsi sekaligus butuh memori lebih besar dari
        // default. Ini aman karena impor hanya dijalankan di mesin lokal.
        $sebelumnya = ini_get('memory_limit');
        ini_set('memory_limit', '1024M');

        $this->info('Membaca Data Rapor Pendidikan: '.basename($path));

        try {
            $impor = $parser->impor($path, function (string $sheet, int $i, int $total) {
                $this->line("  [{$i}/{$total}] {$sheet}");
            });
        } finally {
            ini_set('memory_limit', (string) $sebelumnya);
        }

        $this->info("Selesai. Edisi {$impor->tahun_edisi}, {$impor->jumlah_baris} baris data diproses.");
        if ($impor->catatan_galat !== null) {
            $this->warn($impor->catatan_galat);
        }

        return self::SUCCESS;
    }

    private function tolakEkstensi(string $ekstensi): int
    {
        $this->error("Ekstensi berkas '.{$ekstensi}' tidak didukung. Gunakan .csv atau .xlsx.");

        return self::FAILURE;
    }
}

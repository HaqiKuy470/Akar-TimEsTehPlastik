<?php

declare(strict_types=1);

namespace App\Console\Commands;

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
 * Tahap saat ini: impor berkas Metadata indikator (CSV). Impor sheet provinsi
 * (XLSX) menyusul pada tahap berikutnya.
 */
class ImporRaporCommand extends Command
{
    protected $signature = 'akar:impor {path : Path berkas Metadata (.csv) atau Rapor Pendidikan (.xlsx)}';

    protected $description = 'Impor berkas Rapor Pendidikan ke basis data lokal';

    public function handle(MetadataIndikatorParser $metadataParser): int
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
                'xlsx' => $this->belumDidukung(),
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

    private function belumDidukung(): int
    {
        $this->warn('Impor berkas .xlsx (sheet provinsi) belum tersedia pada tahap ini.');

        return self::FAILURE;
    }

    private function tolakEkstensi(string $ekstensi): int
    {
        $this->error("Ekstensi berkas '.{$ekstensi}' tidak didukung. Gunakan .csv atau .xlsx.");

        return self::FAILURE;
    }
}

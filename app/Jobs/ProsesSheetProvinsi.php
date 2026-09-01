<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ImporBerkas;
use App\Services\Akar\Parsers\CapaianDaerahParser;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * Memproses satu sheet provinsi dari berkas Data Rapor Pendidikan.
 *
 * Satu job per sheet (ARCHITECTURE.md bagian 4.3) supaya sheet yang gagal bisa
 * diulang sendiri tanpa mengulang seluruh berkas.
 */
class ProsesSheetProvinsi implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public int $timeout = 600;

    public int $tries = 2;

    public function __construct(
        public readonly string $path,
        public readonly string $namaSheet,
        public readonly int $imporId,
        public readonly int $tahun,
    ) {}

    public function handle(CapaianDaerahParser $parser): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $impor = ImporBerkas::find($this->imporId);
        if ($impor === null) {
            return;
        }

        // Satu sheet provinsi butuh ~300 MB; aman karena impor daerah hanya di lokal.
        @ini_set('memory_limit', '1024M');

        // Bersihkan dulu supaya percobaan ulang tidak menggandakan baris.
        $parser->bersihkanSheet($this->imporId, $this->namaSheet);

        $jumlah = $parser->imporSheet($this->path, $this->namaSheet, $impor, $this->tahun);

        $impor->increment('jumlah_baris', $jumlah);
    }

    public function failed(Throwable $e): void
    {
        $impor = ImporBerkas::find($this->imporId);
        if ($impor === null) {
            return;
        }

        $baris = "{$this->namaSheet}: {$e->getMessage()}";
        $impor->update([
            'catatan_galat' => trim(($impor->catatan_galat ? $impor->catatan_galat."\n" : '').$baris),
        ]);
    }
}

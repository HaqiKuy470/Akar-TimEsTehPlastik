<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ImporBerkas;
use App\Services\Akar\Parsers\CapaianDaerahParser;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

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

        @ini_set('memory_limit', '1024M');

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

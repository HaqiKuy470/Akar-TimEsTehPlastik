<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ImporBerkas;
use App\Services\Akar\Parsers\CapaianSekolahParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProsesImporSekolah implements ShouldQueue
{
    use Queueable;

    /** @param  string  $lokasiRelatif  path berkas pada disk `local` */
    public function __construct(
        public int $imporId,
        public string $lokasiRelatif,
    ) {}

    public function handle(CapaianSekolahParser $parser): void
    {
        $impor = ImporBerkas::findOrFail($this->imporId);
        $path = Storage::disk('local')->path($this->lokasiRelatif);

        try {
            $parser->imporKe($impor, $path);
        } finally {
            Storage::disk('local')->delete($this->lokasiRelatif);
        }
    }

    public function failed(Throwable $e): void
    {
        ImporBerkas::whereKey($this->imporId)->update([
            'status' => 'gagal',
            'catatan_galat' => 'Berkas tidak dapat diproses. '.$e->getMessage(),
        ]);
    }
}

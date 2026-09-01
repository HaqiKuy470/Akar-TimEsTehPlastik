<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Services\Akar\Parsers\CapaianDaerahParser;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Throwable;

class ProsesImporBerkas implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(public readonly string $path) {}

    public function handle(CapaianDaerahParser $parser): void
    {
        if (! is_file($this->path)) {
            throw new \RuntimeException("Berkas tidak ditemukan: {$this->path}");
        }

        $hash = hash_file('sha256', $this->path);
        $impor = ImporBerkas::firstOrNew(['hash_berkas' => $hash]);

        if ($impor->exists && $impor->status === 'selesai') {
            return;
        }

        $sheet = $parser->sheetProvinsi($this->path);
        if ($sheet === []) {
            $impor->fill([
                'nama_berkas' => basename($this->path),
                'jenis' => 'daerah',
                'status' => 'gagal',
                'catatan_galat' => 'Berkas tidak memuat satu pun sheet provinsi.',
            ])->save();

            return;
        }

        $tahun = $parser->deteksiTahunBerkas($this->path);

        $impor->fill([
            'nama_berkas' => basename($this->path),
            'jenis' => 'daerah',
            'tahun_edisi' => $tahun,
            'status' => 'proses',
            'jumlah_baris' => 0,
            'catatan_galat' => null,
            'diproses_pada' => null,
        ])->save();

        Capaian::where('impor_id', $impor->id)->delete();

        $imporId = $impor->id;
        $path = $this->path;

        $jobs = array_map(
            fn (string $namaSheet) => new ProsesSheetProvinsi($path, $namaSheet, $imporId, $tahun),
            $sheet,
        );

        Bus::batch($jobs)
            ->name("Impor Rapor Pendidikan: {$impor->nama_berkas}")
            ->allowFailures()
            ->finally(fn (Batch $batch) => self::selesaikan($imporId, $batch->failedJobs, $batch->totalJobs))
            ->dispatch();
    }

    public static function selesaikan(int $imporId, int $gagal, int $total): void
    {
        $impor = ImporBerkas::find($imporId);
        if ($impor === null) {
            return;
        }

        $semuaGagal = $total > 0 && $gagal >= $total;

        $catatan = $impor->catatan_galat;
        if ($gagal > 0 && ! $semuaGagal) {
            $ringkas = "{$gagal} dari {$total} sheet gagal diproses.";
            $catatan = $catatan ? $ringkas."\n".$catatan : $ringkas;
        }

        $impor->update([
            'status' => $semuaGagal ? 'gagal' : 'selesai',
            'diproses_pada' => now(),
            'catatan_galat' => $catatan,
        ]);
    }

    public function failed(Throwable $e): void
    {
        if (! is_file($this->path)) {
            return;
        }

        $hash = hash_file('sha256', $this->path);
        ImporBerkas::where('hash_berkas', $hash)->update([
            'status' => 'gagal',
            'catatan_galat' => $e->getMessage(),
        ]);
    }
}

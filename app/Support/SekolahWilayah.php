<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Capaian;
use App\Models\Wilayah;
use Illuminate\Support\Collection;

/**
 * Menjawab "sekolah mana di kabupaten X yang sudah punya data?", agar analis
 * dinas bisa memakai capaian sekolah sebagai konteks. Hanya sekolah yang
 * berkasnya sudah diunggah dan diproses yang muncul, bukan seluruh sekolah.
 */
class SekolahWilayah
{
    /**
     * @return Collection<int, array{
     *   wilayah_id: int,
     *   nama: string,
     *   jenis_satuan: string,
     *   status_satuan: string,
     *   tahun: int,
     *   merah: int, kuning: int, hijau: int
     * }>
     */
    public function diKabupaten(int $kabkotaId): Collection
    {
        $labelMerah = (array) config('akar.label_merah', ['Kurang']);
        $labelKuning = (array) config('akar.label_kuning', ['Sedang']);
        $labelHijau = (array) config('akar.label_hijau', ['Baik']);

        $sekolah = Wilayah::query()
            ->where('level', 'satuan')
            ->where('induk_id', $kabkotaId)
            ->orderBy('nama_satuan')
            ->get(['id', 'nama_satuan']);

        return $sekolah
            ->map(function (Wilayah $s) use ($labelMerah, $labelKuning, $labelHijau) {
                $baris = Capaian::query()
                    ->where('wilayah_id', $s->id)
                    ->join('impor_berkas', 'impor_berkas.id', '=', 'capaian.impor_id')
                    ->where('impor_berkas.jenis', 'satuan')
                    ->where('impor_berkas.status', 'selesai')
                    ->get(['capaian.tahun', 'capaian.jenis_satuan', 'capaian.status_satuan', 'capaian.label_capaian']);

                if ($baris->isEmpty()) {
                    return null;
                }

                $pertama = $baris->first();

                return [
                    'wilayah_id' => $s->id,
                    'nama' => (string) $s->nama_satuan,
                    'jenis_satuan' => (string) $pertama->jenis_satuan,
                    'status_satuan' => (string) $pertama->status_satuan,
                    'tahun' => (int) $pertama->tahun,
                    'merah' => $baris->whereIn('label_capaian', $labelMerah)->count(),
                    'kuning' => $baris->whereIn('label_capaian', $labelKuning)->count(),
                    'hijau' => $baris->whereIn('label_capaian', $labelHijau)->count(),
                ];
            })
            ->filter()
            ->values();
    }
}

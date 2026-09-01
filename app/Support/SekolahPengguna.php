<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\User;
use App\Models\Wilayah;

class SekolahPengguna
{
    public function imporTerakhir(?User $pengguna): ?ImporBerkas
    {
        if ($pengguna === null) {
            return null;
        }

        return ImporBerkas::query()
            ->where('jenis', 'satuan')
            ->where('diunggah_oleh', $pengguna->id)
            ->where('status', 'selesai')
            ->latest('id')
            ->first();
    }

    public function untuk(?User $pengguna): ?Wilayah
    {
        $impor = $this->imporTerakhir($pengguna);
        if ($impor === null) {
            return null;
        }

        $wilayahId = Capaian::query()
            ->where('impor_id', $impor->id)
            ->value('wilayah_id');

        return $wilayahId !== null ? Wilayah::find($wilayahId) : null;
    }

    /** @return list<array{tahun: int, jenis_satuan: string, status_satuan: string}> */
    public function kombinasi(Wilayah $sekolah): array
    {
        return Capaian::query()
            ->where('wilayah_id', $sekolah->id)
            ->select('tahun', 'jenis_satuan', 'status_satuan')
            ->distinct()
            ->orderByDesc('tahun')
            ->orderBy('jenis_satuan')
            ->orderBy('status_satuan')
            ->get()
            ->map(fn ($b) => [
                'tahun' => (int) $b->tahun,
                'jenis_satuan' => (string) $b->jenis_satuan,
                'status_satuan' => (string) $b->status_satuan,
            ])
            ->all();
    }
}

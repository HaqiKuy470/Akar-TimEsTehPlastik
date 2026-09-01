<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\User;
use App\Models\Wilayah;

/**
 * Menjawab "sekolah mana milik pengguna ini?". Kaitan pengguna-sekolah tidak
 * disimpan di tabel users, melainkan diturunkan dari berkas yang pernah ia
 * unggah sendiri (impor_berkas.diunggah_oleh), sehingga data sekolah lain tetap
 * tak terjangkau.
 */
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

    /** Wilayah level 'satuan' milik pengguna, atau null bila belum ada berkas berhasil. */
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

    /**
     * Kombinasi jenjang dan status pada berkas sekolah (biasanya satu).
     *
     * @return list<array{tahun: int, jenis_satuan: string, status_satuan: string}>
     */
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

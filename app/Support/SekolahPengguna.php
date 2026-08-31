<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\User;
use App\Models\Wilayah;

/**
 * Menjawab pertanyaan "sekolah mana milik pengguna ini?".
 *
 * Mode satuan pendidikan tidak menautkan pengguna ke sekolah lewat kolom
 * khusus di tabel users; kaitannya diambil dari berkas yang pernah diunggah
 * pengguna sendiri (impor_berkas.diunggah_oleh). Dengan begitu satu kepala
 * sekolah yang mengunggah berkas SMP-nya akan selalu melihat data SMP itu,
 * dan tidak ada data sekolah lain yang bisa ia buka.
 */
class SekolahPengguna
{
    /**
     * Catatan impor berkas satuan terakhir yang berhasil milik pengguna.
     */
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

    /**
     * Wilayah level 'satuan' milik pengguna, atau null bila ia belum pernah
     * mengunggah berkas yang berhasil diproses.
     */
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
     * Kombinasi jenjang dan status yang ada pada berkas sekolah (biasanya satu).
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

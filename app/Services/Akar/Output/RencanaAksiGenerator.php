<?php

declare(strict_types=1);

namespace App\Services\Akar\Output;

use App\Enums\Keyakinan;
use App\Models\Analisis;
use App\Models\AnalisisAkar;
use App\Models\AnalisisPrioritas;
use App\Models\Capaian;
use App\Models\RencanaAksi;
use Illuminate\Support\Facades\DB;

/**
 * F7 - Generator Rencana Tindak Lanjut: hasil analisis -> draf dokumen kerja,
 * satu baris per kegiatan usulan (penanggung jawab, indikator keberhasilan, waktu).
 *
 * Kegiatan per akar masalah dari config/intervensi.php; rincian dari
 * config/kegiatan.php. Prioritas tanpa akar berbukti cukup, atau akar yang
 * kegiatannya tak ada di katalog, dilewati (tidak mengarang). Hasilnya draf yang
 * dapat disunting pengguna.
 */
class RencanaAksiGenerator
{
    /**
     * Susun (atau susun ulang) draf rencana tindak lanjut. Idempoten: bila sudah
     * ada dan $paksaUlang false, rencana lama dikembalikan agar suntingan pengguna
     * tidak hilang.
     */
    public function hasilkan(Analisis $analisis, ?int $dibuatOleh = null, bool $paksaUlang = false): RencanaAksi
    {
        $analisis->loadMissing('wilayah', 'prioritas.indikator', 'prioritas.akar');

        $rencana = RencanaAksi::firstOrNew(['analisis_id' => $analisis->id]);

        if ($rencana->exists && ! $paksaUlang) {
            return $rencana;
        }

        return DB::transaction(function () use ($analisis, $dibuatOleh, $rencana) {
            $rencana->fill([
                'judul' => 'Rencana Tindak Lanjut '.$analisis->wilayah?->namaTampilan().' '.$analisis->tahun,
                'dibuat_oleh' => $dibuatOleh ?? $rencana->dibuat_oleh,
            ])->save();

            $rencana->item()->delete();

            $intervensi = (array) config('intervensi', []);
            $katalog = (array) config('kegiatan', []);
            $labelIndikator = $this->labelIndikator($analisis);

            $urutan = 0;
            $baris = [];

            foreach ($analisis->prioritas as $prioritas) {
                $nomor = $prioritas->indikator?->nomor;
                $akar = $this->akarTerkuat($prioritas);

                if ($nomor === null || $akar === null || ! isset($intervensi[$nomor])) {
                    continue;
                }

                $kandidat = $this->kandidatUntuk($intervensi[$nomor], $akar->kode_akar);
                if ($kandidat === null) {
                    continue;
                }

                $masalah = trim(sprintf(
                    '%s berlabel %s',
                    $prioritas->indikator->nama,
                    $labelIndikator[$prioritas->indikator_id] ?? 'Kurang',
                ));

                foreach ($kandidat['kegiatan'] ?? [] as $kodeKegiatan) {
                    $k = $katalog[$kodeKegiatan] ?? null;
                    if ($k === null) {
                        continue; // kegiatan belum ada di katalog, jangan mengarang
                    }

                    $baris[] = [
                        'rencana_aksi_id' => $rencana->id,
                        'masalah' => $masalah,
                        'akar_masalah' => $akar->label,
                        'kegiatan' => trim($k['nama'].' — '.$k['deskripsi']),
                        'penanggung_jawab' => $k['penanggung_jawab'] ?? null,
                        'indikator_keberhasilan' => $k['indikator_keberhasilan'] ?? null,
                        'perkiraan_waktu' => $k['perkiraan_waktu'] ?? null,
                        'urutan' => $urutan++,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if ($baris !== []) {
                DB::table('rencana_aksi_item')->insert($baris);
            }

            return $rencana->load('item');
        });
    }

    /** Akar keyakinan tertinggi yang bukan "tidak cukup bukti"; null bila tidak ada. */
    private function akarTerkuat(AnalisisPrioritas $prioritas): ?AnalisisAkar
    {
        return $prioritas->akar
            ->reject(fn (AnalisisAkar $a) => $a->keyakinan === Keyakinan::TidakCukupBukti)
            ->sortBy(fn (AnalisisAkar $a) => $this->urutanKeyakinan($a->keyakinan))
            ->first();
    }

    private function urutanKeyakinan(Keyakinan $keyakinan): int
    {
        return match ($keyakinan) {
            Keyakinan::Kuat => 0,
            Keyakinan::Sedang => 1,
            Keyakinan::Lemah => 2,
            Keyakinan::TidakCukupBukti => 3,
        };
    }

    /**
     * Kandidat akar di config/intervensi.php yang kodenya cocok.
     *
     * @param  array<string, mixed>  $entriIndikator
     * @return array<string, mixed>|null
     */
    private function kandidatUntuk(array $entriIndikator, string $kodeAkar): ?array
    {
        foreach ($entriIndikator['kandidat_akar'] ?? [] as $kandidat) {
            if (($kandidat['kode'] ?? null) === $kodeAkar) {
                return $kandidat;
            }
        }

        return null;
    }

    /**
     * Label capaian tiap indikator prioritas, sekali kueri.
     *
     * @return array<int, string> indikator_id => label capaian
     */
    private function labelIndikator(Analisis $analisis): array
    {
        return Capaian::query()
            ->where('wilayah_id', $analisis->wilayah_id)
            ->where('tahun', $analisis->tahun)
            ->where('jenis_satuan', $analisis->jenis_satuan)
            ->where('status_satuan', $analisis->status_satuan)
            ->whereIn('indikator_id', $analisis->prioritas->pluck('indikator_id'))
            ->pluck('label_capaian', 'indikator_id')
            ->all();
    }
}

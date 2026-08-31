<?php

declare(strict_types=1);

namespace App\Http\Livewire\Sekolah;

use App\Models\Analisis;
use App\Models\AnalisisAkar;
use App\Models\AnalisisPrioritas;
use App\Models\Capaian;
use App\Services\Akar\Analysis\AkarMasalahAnalyzer;
use App\Services\Akar\Analysis\AnalisisRunner;
use App\Support\SekolahPengguna;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Prioritas masalah dan akar masalah untuk satu sekolah.
 *
 * Memakai layanan yang sama dengan versi dinas (AnalisisRunner,
 * AkarMasalahAnalyzer). Perbedaannya hanya wilayah: tetap sekolah milik
 * pengguna, tanpa pemilih. Komponen "posisi relatif" pada skor prioritas
 * membandingкан sekolah dengan agregat kabupatennya, bukan dengan sekolah
 * lain (data sekolah lain tidak dipublikasikan).
 */
class Prioritas extends Component
{
    /** @var list<int> */
    public array $rincianTerbuka = [];

    /** @var list<int> */
    public array $akarTerbuka = [];

    #[Computed]
    public function sekolah()
    {
        return app(SekolahPengguna::class)->untuk(auth()->user());
    }

    /**
     * @return array{tahun: int, jenis_satuan: string, status_satuan: string}|null
     */
    #[Computed]
    public function kombinasi(): ?array
    {
        $sekolah = $this->sekolah;
        if ($sekolah === null) {
            return null;
        }

        return app(SekolahPengguna::class)->kombinasi($sekolah)[0] ?? null;
    }

    #[Computed]
    public function analisis(): ?Analisis
    {
        $sekolah = $this->sekolah;
        $kombinasi = $this->kombinasi;
        if ($sekolah === null || $kombinasi === null) {
            return null;
        }

        return Analisis::query()
            ->where('wilayah_id', $sekolah->id)
            ->where('tahun', $kombinasi['tahun'])
            ->where('jenis_satuan', $kombinasi['jenis_satuan'])
            ->where('status_satuan', $kombinasi['status_satuan'])
            ->latest('id')
            ->with(['prioritas' => fn ($q) => $q->orderBy('peringkat'), 'prioritas.indikator'])
            ->first();
    }

    public function jalankan(AnalisisRunner $runner): void
    {
        $sekolah = $this->sekolah;
        $kombinasi = $this->kombinasi;
        if ($sekolah === null || $kombinasi === null) {
            return;
        }

        $runner->jalankan(
            $sekolah,
            $kombinasi['tahun'],
            $kombinasi['jenis_satuan'],
            $kombinasi['status_satuan'],
            auth()->id(),
        );

        $this->rincianTerbuka = [];
        $this->akarTerbuka = [];
        unset($this->analisis);
    }

    public function toggleRincian(int $prioritasId): void
    {
        $this->rincianTerbuka = $this->toggle($this->rincianTerbuka, $prioritasId);
    }

    public function toggleAkar(int $prioritasId, AkarMasalahAnalyzer $analyzer): void
    {
        $this->akarTerbuka = $this->toggle($this->akarTerbuka, $prioritasId);

        if (in_array($prioritasId, $this->akarTerbuka, true)) {
            $prioritas = AnalisisPrioritas::with('indikator', 'analisis')->find($prioritasId);
            if ($prioritas !== null) {
                $analyzer->telusuri($prioritas);
            }
        }
    }

    /**
     * @param  list<int>  $daftar
     * @return list<int>
     */
    private function toggle(array $daftar, int $id): array
    {
        return in_array($id, $daftar, true)
            ? array_values(array_diff($daftar, [$id]))
            : [...$daftar, $id];
    }

    public function render()
    {
        $analisis = $this->analisis;

        return view('livewire.sekolah.prioritas', [
            'daftar' => $analisis !== null ? $this->susunDaftar($analisis) : [],
            'sudahDijalankan' => $analisis !== null,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function susunDaftar(Analisis $analisis): array
    {
        $prioritas = $analisis->prioritas;
        if ($prioritas->isEmpty()) {
            return [];
        }

        $capaian = Capaian::query()
            ->where('wilayah_id', $analisis->wilayah_id)
            ->where('tahun', $analisis->tahun)
            ->where('jenis_satuan', $analisis->jenis_satuan)
            ->where('status_satuan', $analisis->status_satuan)
            ->whereIn('indikator_id', $prioritas->pluck('indikator_id'))
            ->get(['indikator_id', 'label_capaian', 'perubahan_nilai'])
            ->keyBy('indikator_id');

        return $prioritas->map(function (AnalisisPrioritas $p) use ($capaian) {
            $baris = $capaian->get($p->indikator_id);

            $item = [
                'id' => $p->id,
                'peringkat_prioritas' => $p->peringkat,
                'nomor' => $p->indikator?->nomor ?? '—',
                'nama' => $p->indikator?->nama ?? 'Indikator tidak dikenal',
                'skor' => $p->skor,
                'label' => $baris->label_capaian ?? 'Tidak Tersedia',
                'perubahan' => $baris->perubahan_nilai ?? 'Tidak Tersedia',
                'kalimat_penjelas' => $p->kalimat_penjelas ?: 'Indikator ini berlabel merah dan masuk daftar prioritas.',
                'komponen_skor' => $p->komponen_skor ?? [],
                'rincian_terbuka' => in_array($p->id, $this->rincianTerbuka, true),
                'akar_terbuka' => in_array($p->id, $this->akarTerbuka, true),
                'akar' => null,
            ];

            if ($item['akar_terbuka']) {
                $item['akar'] = $this->susunAkar($p->id, $item['label']);
            }

            return $item;
        })->all();
    }

    /**
     * @return array{dipetakan: bool, induk_label: string, kandidat: list<array<string, mixed>>}
     */
    private function susunAkar(int $prioritasId, string $indukLabel): array
    {
        $akar = AnalisisAkar::query()->where('analisis_prioritas_id', $prioritasId)->get();

        return [
            'dipetakan' => $akar->isNotEmpty(),
            'induk_label' => $indukLabel,
            'kandidat' => $akar->map(fn (AnalisisAkar $a) => [
                'label' => $a->label,
                'keyakinan' => $a->keyakinan->label(),
                'keyakinan_kode' => $a->keyakinan->value,
                'bukti' => $a->bukti ?? [],
            ])->all(),
        ];
    }
}

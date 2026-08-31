<?php

declare(strict_types=1);

namespace App\Http\Livewire\Dinas;

use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\Analysis\BenchmarkService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * F5 — Perbandingan Antardaerah.
 *
 * Komponen ini hanya mengurus pilihan pengguna dan menampilkan hasil.
 * Seluruh perhitungan peringkat ada di BenchmarkService, sesuai aturan
 * pemisahan logika di CLAUDE.md.
 */
class Perbandingan extends Component
{
    #[Url]
    public ?int $tahun = null;

    #[Url]
    public string $provinsi = '';

    #[Url]
    public ?int $wilayahId = null;

    #[Url]
    public string $jenisSatuan = '';

    #[Url]
    public string $statusSatuan = '';

    #[Url]
    public ?int $indikatorId = null;

    /** Kolom pengurutan tabel peringkat: 'peringkat' atau 'nama'. */
    public string $urutKolom = 'peringkat';

    /** Arah pengurutan: 'asc' atau 'desc'. */
    public string $urutArah = 'asc';

    public function mount(): void
    {
        $this->tahun ??= $this->tahunTersedia()->first();
    }

    public function updatedProvinsi(): void
    {
        $this->wilayahId = null;
    }

    public function updatedTahun(): void
    {
        $this->jenisSatuan = '';
        $this->statusSatuan = '';
        $this->indikatorId = null;
    }

    public function updatedJenisSatuan(): void
    {
        $this->statusSatuan = '';
        $this->indikatorId = null;
    }

    /**
     * Klik header tabel: kolom sama membalik arah, kolom lain mulai menaik.
     */
    public function urutkan(string $kolom): void
    {
        if ($this->urutKolom === $kolom) {
            $this->urutArah = $this->urutArah === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->urutKolom = $kolom;
        $this->urutArah = 'asc';
    }

    /**
     * @return Collection<int, int>
     */
    #[Computed]
    public function tahunTersedia(): Collection
    {
        return ImporBerkas::query()
            ->where('jenis', 'daerah')
            ->where('status', 'selesai')
            ->whereNotNull('tahun_edisi')
            ->orderByDesc('tahun_edisi')
            ->distinct()
            ->pluck('tahun_edisi');
    }

    /**
     * @return Collection<int, string>
     */
    #[Computed]
    public function provinsiTersedia(): Collection
    {
        return Wilayah::query()
            ->where('level', 'kabkota')
            ->whereNotNull('provinsi')
            ->orderBy('provinsi')
            ->distinct()
            ->pluck('provinsi');
    }

    /**
     * @return Collection<int, Wilayah>
     */
    #[Computed]
    public function kabkotaTersedia(): Collection
    {
        if ($this->provinsi === '') {
            return collect();
        }

        return Wilayah::query()
            ->where('level', 'kabkota')
            ->where('provinsi', $this->provinsi)
            ->orderBy('kabupaten_kota')
            ->get(['id', 'kabupaten_kota']);
    }

    /**
     * @return Collection<int, string>
     */
    #[Computed]
    public function jenisSatuanTersedia(): Collection
    {
        if ($this->tahun === null) {
            return collect();
        }

        return Capaian::query()
            ->where('tahun', $this->tahun)
            ->orderBy('jenis_satuan')
            ->distinct()
            ->pluck('jenis_satuan');
    }

    /**
     * @return Collection<int, string>
     */
    #[Computed]
    public function statusSatuanTersedia(): Collection
    {
        if ($this->tahun === null || $this->jenisSatuan === '') {
            return collect();
        }

        return Capaian::query()
            ->where('tahun', $this->tahun)
            ->where('jenis_satuan', $this->jenisSatuan)
            ->orderBy('status_satuan')
            ->distinct()
            ->pluck('status_satuan');
    }

    /**
     * Indikator yang punya data untuk kombinasi tahun dan jenjang terpilih,
     * diurutkan secara natural menurut nomor ("A.2" sebelum "A.10").
     *
     * @return Collection<int, Indikator>
     */
    #[Computed]
    public function indikatorTersedia(): Collection
    {
        if ($this->tahun === null || $this->jenisSatuan === '') {
            return collect();
        }

        $id = Capaian::query()
            ->where('tahun', $this->tahun)
            ->where('jenis_satuan', $this->jenisSatuan)
            ->distinct()
            ->pluck('indikator_id');

        return Indikator::query()
            ->whereIn('id', $id)
            ->get(['id', 'nomor', 'nama'])
            ->sortBy(fn (Indikator $i) => preg_replace_callback(
                '/\d+/',
                fn ($m) => str_pad($m[0], 4, '0', STR_PAD_LEFT),
                $i->nomor,
            ), SORT_NATURAL)
            ->values();
    }

    /**
     * Indikator yang sedang ditampilkan: pilihan pengguna, atau indikator
     * pertama yang tersedia bila belum memilih.
     */
    #[Computed]
    public function indikatorAktif(): ?Indikator
    {
        $daftar = $this->indikatorTersedia;

        if ($this->indikatorId !== null) {
            $terpilih = $daftar->firstWhere('id', $this->indikatorId);
            if ($terpilih !== null) {
                return $terpilih;
            }
        }

        return $daftar->first();
    }

    /**
     * @return array{
     *   indikator: array{nomor: string, nama: string},
     *   peringkat: array<string, mixed>,
     *   pembanding: array<string, mixed>,
     *   tabel: list<array<string, mixed>>
     * }|null
     */
    #[Computed]
    public function hasil(): ?array
    {
        $indikator = $this->indikatorAktif;

        if ($this->tahun === null || $this->wilayahId === null || $this->jenisSatuan === '' || $this->statusSatuan === '' || $indikator === null) {
            return null;
        }

        $wilayah = Wilayah::find($this->wilayahId);
        if ($wilayah === null) {
            return null;
        }

        $benchmark = app(BenchmarkService::class);

        return [
            'indikator' => ['nomor' => $indikator->nomor, 'nama' => $indikator->nama],
            'peringkat' => $benchmark->peringkat($wilayah, $indikator, $this->tahun, $this->jenisSatuan, $this->statusSatuan),
            'pembanding' => $benchmark->pembanding($wilayah, $indikator, $this->tahun, $this->jenisSatuan, $this->statusSatuan),
            'tabel' => $this->urutkanTabel(
                $benchmark->tabelPeringkat($wilayah->provinsi ?? $this->provinsi, $indikator, $this->tahun, $this->jenisSatuan, $this->statusSatuan)
            ),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $tabel
     * @return list<array<string, mixed>>
     */
    private function urutkanTabel(array $tabel): array
    {
        $arah = $this->urutArah === 'desc' ? -1 : 1;

        usort($tabel, function ($a, $b) use ($arah) {
            if ($this->urutKolom === 'nama') {
                $selisih = strcmp((string) $a['nama'], (string) $b['nama']);
            } else {
                $selisih = ($a['peringkat'] <=> $b['peringkat'])
                    ?: strcmp((string) $a['nama'], (string) $b['nama']);
            }

            return $selisih * $arah;
        });

        return $tabel;
    }

    public function render()
    {
        return view('livewire.dinas.perbandingan');
    }
}

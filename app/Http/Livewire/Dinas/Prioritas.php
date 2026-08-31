<?php

declare(strict_types=1);

namespace App\Http\Livewire\Dinas;

use App\Models\Analisis;
use App\Models\AnalisisAkar;
use App\Models\AnalisisPrioritas;
use App\Models\Capaian;
use App\Models\ImporBerkas;
use App\Models\Indikator;
use App\Models\Wilayah;
use App\Services\Akar\Analysis\AkarMasalahAnalyzer;
use App\Services\Akar\Analysis\AnalisisRunner;
use App\Services\Akar\Analysis\BenchmarkService;
use App\Services\Akar\Output\LaporanExporter;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * F3 — Deteksi & Prioritisasi Masalah, dengan penelusuran akar masalah (F4)
 * sebagai rincian di tiap kartu prioritas.
 *
 * Komponen ini hanya mengurus pilihan pengguna, memicu layanan, dan menyusun
 * tampilan. Seluruh perhitungan skor, pemeringkatan, dan penelusuran akar
 * masalah berada di app/Services/Akar/, sesuai aturan pemisahan logika di
 * CLAUDE.md.
 */
class Prioritas extends Component
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

    /** Id analisis_prioritas yang rincian skornya sedang dibuka. @var list<int> */
    public array $rincianTerbuka = [];

    /** Id analisis_prioritas yang akar masalahnya sedang ditelusuri. @var list<int> */
    public array $akarTerbuka = [];

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
    }

    /**
     * Panel yang terbuka milik kombinasi lama; tutup semuanya saat pilihan
     * berganti supaya tidak menampilkan rincian dari analisis yang salah.
     */
    public function updated(string $name): void
    {
        if (in_array($name, ['tahun', 'provinsi', 'wilayahId', 'jenisSatuan', 'statusSatuan'], true)) {
            $this->rincianTerbuka = [];
            $this->akarTerbuka = [];
        }
    }

    // ------------------------------------------------------------------
    // Pilihan wilayah / tahun / jenjang / status
    // ------------------------------------------------------------------

    /** @return Collection<int, int> */
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

    /** @return Collection<int, string> */
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

    /** @return Collection<int, Wilayah> */
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

    /** @return Collection<int, string> */
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

    /** @return Collection<int, string> */
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

    #[Computed]
    public function siapDijalankan(): bool
    {
        return $this->tahun !== null
            && $this->wilayahId !== null
            && $this->jenisSatuan !== ''
            && $this->statusSatuan !== '';
    }

    // ------------------------------------------------------------------
    // Analisis
    // ------------------------------------------------------------------

    /**
     * Analisis terakhir untuk kombinasi yang dipilih, bila sudah pernah
     * dijalankan. Menjalankan ulang menambah baris analisis baru dan yang
     * terbaru inilah yang ditampilkan; riwayat lama tetap tersimpan.
     */
    #[Computed]
    public function analisis(): ?Analisis
    {
        if (! $this->siapDijalankan) {
            return null;
        }

        return Analisis::query()
            ->where('wilayah_id', $this->wilayahId)
            ->where('tahun', $this->tahun)
            ->where('jenis_satuan', $this->jenisSatuan)
            ->where('status_satuan', $this->statusSatuan)
            ->latest('id')
            ->with(['prioritas' => fn ($q) => $q->orderBy('peringkat'), 'prioritas.indikator'])
            ->first();
    }

    public function jalankan(AnalisisRunner $runner): void
    {
        if (! $this->siapDijalankan) {
            return;
        }

        $wilayah = Wilayah::find($this->wilayahId);
        if ($wilayah === null) {
            return;
        }

        $runner->jalankan($wilayah, $this->tahun, $this->jenisSatuan, $this->statusSatuan, auth()->id());

        $this->rincianTerbuka = [];
        $this->akarTerbuka = [];
        unset($this->analisis);
    }

    /**
     * Unduh laporan PDF untuk analisis yang sedang ditampilkan. Perakitan
     * dokumen ada di LaporanExporter; komponen ini hanya meneruskan responsnya.
     */
    public function unduhPdf(LaporanExporter $exporter): ?StreamedResponse
    {
        $analisis = $this->analisis;
        if ($analisis === null) {
            return null;
        }

        $pdf = $exporter->pdf($analisis);
        $nama = $exporter->namaBerkas($analisis, 'pdf');

        return response()->streamDownload(
            fn () => print $pdf->output(),
            $nama,
            ['Content-Type' => 'application/pdf'],
        );
    }

    /**
     * Unduh data mentah analisis sebagai berkas Excel.
     */
    public function unduhExcel(LaporanExporter $exporter): ?Response
    {
        $analisis = $this->analisis;
        if ($analisis === null) {
            return null;
        }

        return Excel::download(
            $exporter->excel($analisis),
            $exporter->namaBerkas($analisis, 'xlsx'),
        );
    }

    public function toggleRincian(int $prioritasId): void
    {
        $this->rincianTerbuka = $this->toggle($this->rincianTerbuka, $prioritasId);
    }

    public function toggleAkar(int $prioritasId, AkarMasalahAnalyzer $analyzer): void
    {
        $this->akarTerbuka = $this->toggle($this->akarTerbuka, $prioritasId);

        // Telusuri hanya saat panel dibuka; hasilnya disimpan dan dibaca ulang
        // saat render, sehingga proses ini tidak berjalan tiap render.
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

    // ------------------------------------------------------------------
    // Penyusunan tampilan
    // ------------------------------------------------------------------

    public function render()
    {
        $analisis = $this->analisis;

        return view('livewire.dinas.prioritas', [
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

        // Label & arah perubahan tiap indikator prioritas dalam satu kueri.
        $capaian = Capaian::query()
            ->where('wilayah_id', $analisis->wilayah_id)
            ->where('tahun', $analisis->tahun)
            ->where('jenis_satuan', $analisis->jenis_satuan)
            ->where('status_satuan', $analisis->status_satuan)
            ->whereIn('indikator_id', $prioritas->pluck('indikator_id'))
            ->get(['indikator_id', 'label_capaian', 'perubahan_nilai'])
            ->keyBy('indikator_id');

        $wilayah = $analisis->wilayah()->first();
        $benchmark = app(BenchmarkService::class);

        return $prioritas->map(function (AnalisisPrioritas $p) use ($analisis, $capaian, $wilayah, $benchmark) {
            $baris = $capaian->get($p->indikator_id);
            $label = $baris->label_capaian ?? 'Tidak Tersedia';
            $perubahan = $baris->perubahan_nilai ?? 'Tidak Tersedia';

            $peringkat = null;
            if ($wilayah !== null && $p->indikator !== null) {
                $b = $benchmark->peringkat(
                    $wilayah, $p->indikator, $analisis->tahun, $analisis->jenis_satuan, $analisis->status_satuan
                );
                if ($b['peringkat'] !== null) {
                    $peringkat = $b['peringkat'] === $b['peringkat_hingga']
                        ? "Peringkat {$b['peringkat']} dari {$b['dari']}"
                        : "Peringkat {$b['peringkat']}\u{2013}{$b['peringkat_hingga']} dari {$b['dari']}";
                }
            }

            $item = [
                'id' => $p->id,
                'peringkat_prioritas' => $p->peringkat,
                'nomor' => $p->indikator?->nomor ?? '—',
                'nama' => $p->indikator?->nama ?? 'Indikator tidak dikenal',
                'skor' => $p->skor,
                'label' => $label,
                'perubahan' => $perubahan,
                'peringkat_teks' => $peringkat,
                'kalimat_penjelas' => $p->kalimat_penjelas ?: 'Indikator ini berlabel merah dan masuk daftar prioritas.',
                'komponen_skor' => $p->komponen_skor ?? [],
                'rincian_terbuka' => in_array($p->id, $this->rincianTerbuka, true),
                'akar_terbuka' => in_array($p->id, $this->akarTerbuka, true),
                'akar' => null,
            ];

            if ($item['akar_terbuka']) {
                $item['akar'] = $this->susunAkar($p->id, $label);
            }

            return $item;
        })->all();
    }

    /**
     * @return array{dipetakan: bool, induk_label: string, kandidat: list<array<string, mixed>>}
     */
    private function susunAkar(int $prioritasId, string $indukLabel): array
    {
        $akar = AnalisisAkar::query()
            ->where('analisis_prioritas_id', $prioritasId)
            ->get();

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

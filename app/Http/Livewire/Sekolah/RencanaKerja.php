<?php

declare(strict_types=1);

namespace App\Http\Livewire\Sekolah;

use App\Models\Analisis;
use App\Models\RencanaAksi;
use App\Models\RencanaAksiItem;
use App\Services\Akar\Output\LaporanExporter;
use App\Services\Akar\Output\RencanaAksiGenerator;
use App\Support\SekolahPengguna;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Rencana Kerja Tahunan (RKT) satu sekolah.
 *
 * Sama seperti generator rencana tindak lanjut tingkat dinas, tetapi diberi
 * istilah RKT karena itulah dokumen yang wajib disusun kepala sekolah setiap
 * tahun (PRD bagian 3.2). Analisisnya diambil otomatis dari analisis terakhir
 * sekolah pengguna; penyusunan draf tetap di RencanaAksiGenerator.
 */
class RencanaKerja extends Component
{
    /** @var list<array<string, mixed>> */
    public array $item = [];

    public ?int $rencanaId = null;

    public string $judul = '';

    public bool $tersimpan = false;

    private const KOLOM = [
        'masalah', 'akar_masalah', 'kegiatan',
        'penanggung_jawab', 'indikator_keberhasilan', 'perkiraan_waktu',
    ];

    public function mount(): void
    {
        $this->muatRencana();
    }

    #[Computed]
    public function sekolah()
    {
        return app(SekolahPengguna::class)->untuk(auth()->user());
    }

    #[Computed]
    public function analisis(): ?Analisis
    {
        $sekolah = $this->sekolah;
        if ($sekolah === null) {
            return null;
        }

        return Analisis::query()
            ->where('wilayah_id', $sekolah->id)
            ->with('wilayah')
            ->latest('id')
            ->first();
    }

    public function susunDraf(): void
    {
        $analisis = $this->analisis;
        if ($analisis === null) {
            return;
        }

        $paksaUlang = RencanaAksi::where('analisis_id', $analisis->id)->exists();
        app(RencanaAksiGenerator::class)->hasilkan($analisis, auth()->id(), $paksaUlang);

        $this->muatRencana();
    }

    public function unduhPdf(LaporanExporter $exporter): ?StreamedResponse
    {
        $analisis = $this->analisis;
        if ($analisis === null) {
            return null;
        }

        $pdf = $exporter->pdf($analisis);

        return response()->streamDownload(
            fn () => print $pdf->output(),
            $exporter->namaBerkas($analisis, 'pdf'),
            ['Content-Type' => 'application/pdf'],
        );
    }

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

    public function tambahBaris(): void
    {
        $this->item[] = array_fill_keys(self::KOLOM, '');
        $this->tersimpan = false;
    }

    public function hapusBaris(int $index): void
    {
        unset($this->item[$index]);
        $this->item = array_values($this->item);
        $this->tersimpan = false;
    }

    public function simpan(): void
    {
        $analisis = $this->analisis;
        if ($analisis === null) {
            return;
        }

        $rencana = RencanaAksi::firstOrCreate(
            ['analisis_id' => $analisis->id],
            [
                'judul' => $this->judul !== ''
                    ? $this->judul
                    : 'Rencana Kerja Tahunan '.$analisis->wilayah?->namaTampilan().' '.$analisis->tahun,
                'dibuat_oleh' => auth()->id(),
            ],
        );

        if ($this->judul !== '' && $this->judul !== $rencana->judul) {
            $rencana->update(['judul' => $this->judul]);
        }

        $rencana->item()->delete();

        foreach (array_values($this->item) as $urutan => $baris) {
            if ($this->barisKosong($baris)) {
                continue;
            }

            $rencana->item()->create([
                'masalah' => $baris['masalah'] ?? '',
                'akar_masalah' => $baris['akar_masalah'] ?? '',
                'kegiatan' => $baris['kegiatan'] ?? '',
                'penanggung_jawab' => $baris['penanggung_jawab'] ?: null,
                'indikator_keberhasilan' => $baris['indikator_keberhasilan'] ?: null,
                'perkiraan_waktu' => $baris['perkiraan_waktu'] ?: null,
                'urutan' => $urutan,
            ]);
        }

        $this->rencanaId = $rencana->id;
        $this->muatRencana();
        $this->tersimpan = true;
    }

    private function muatRencana(): void
    {
        $analisis = $this->analisis;
        $rencana = $analisis !== null
            ? RencanaAksi::with('item')->where('analisis_id', $analisis->id)->first()
            : null;

        if ($rencana === null) {
            $this->rencanaId = null;
            $this->item = [];
            $this->judul = '';

            return;
        }

        $this->rencanaId = $rencana->id;
        $this->judul = $rencana->judul;
        $this->item = $rencana->item
            ->map(fn (RencanaAksiItem $i) => [
                'masalah' => $i->masalah,
                'akar_masalah' => $i->akar_masalah,
                'kegiatan' => $i->kegiatan,
                'penanggung_jawab' => (string) $i->penanggung_jawab,
                'indikator_keberhasilan' => (string) $i->indikator_keberhasilan,
                'perkiraan_waktu' => (string) $i->perkiraan_waktu,
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $baris
     */
    private function barisKosong(array $baris): bool
    {
        foreach ($baris as $nilai) {
            if (trim((string) $nilai) !== '') {
                return false;
            }
        }

        return true;
    }

    public function render()
    {
        return view('livewire.sekolah.rencana-kerja');
    }
}

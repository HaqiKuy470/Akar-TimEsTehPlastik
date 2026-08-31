<?php

declare(strict_types=1);

namespace App\Http\Livewire\Dinas;

use App\Models\Analisis;
use App\Models\RencanaAksi;
use App\Models\RencanaAksiItem;
use App\Services\Akar\Output\LaporanExporter;
use App\Services\Akar\Output\RencanaAksiGenerator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * F7 - Generator Rencana Tindak Lanjut.
 *
 * Komponen ini memilih analisis, meminta RencanaAksiGenerator menyusun draf,
 * lalu menyediakan tabel yang dapat disunting pengguna. Tidak ada logika
 * penyusunan di sini; semuanya di service.
 */
class RencanaTindakLanjut extends Component
{
    #[Url]
    public ?int $analisisId = null;

    /**
     * Baris rencana yang sedang disunting.
     *
     * @var list<array<string, mixed>>
     */
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
        if ($this->analisisId !== null) {
            $this->muatRencana();
        }
    }

    public function updatedAnalisisId(): void
    {
        $this->reset('item', 'rencanaId', 'judul', 'tersimpan');
        $this->muatRencana();
    }

    /**
     * @return Collection<int, array{id: int, label: string}>
     */
    #[Computed]
    public function analisisTersedia(): Collection
    {
        return Analisis::query()
            ->with('wilayah')
            ->latest('id')
            ->get()
            ->map(fn (Analisis $a) => [
                'id' => $a->id,
                'label' => sprintf(
                    '%s · %s · %d',
                    $a->wilayah?->namaTampilan() ?? 'Wilayah',
                    $a->jenis_satuan,
                    $a->tahun,
                ),
            ]);
    }

    #[Computed]
    public function analisis(): ?Analisis
    {
        return $this->analisisId !== null ? Analisis::with('wilayah')->find($this->analisisId) : null;
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

    /**
     * Unduh laporan lengkap (profil, prioritas, akar masalah, rencana) sebagai
     * PDF. Perakitan dokumen ada di LaporanExporter.
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
                    : 'Rencana Tindak Lanjut '.$analisis->wilayah?->namaTampilan().' '.$analisis->tahun,
                'dibuat_oleh' => auth()->id(),
            ],
        );

        if ($this->judul !== '' && $this->judul !== $rencana->judul) {
            $rencana->update(['judul' => $this->judul]);
        }

        // Tabel item dibangun ulang dari keadaan yang disunting: lebih sederhana
        // dan bebas dari masalah sinkronisasi id baris yang ditambah/dihapus.
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
        $rencana = $this->analisisId !== null
            ? RencanaAksi::with('item')->where('analisis_id', $this->analisisId)->first()
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
        return view('livewire.dinas.rencana-tindak-lanjut')
            ->layout('layouts::app', ['header' => 'Rencana tindak lanjut']);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Livewire\Sekolah;

use App\Jobs\ProsesImporSekolah;
use App\Models\ImporBerkas;
use App\Services\Akar\Parsers\DeteksiJenisBerkas;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * F10 — Mode Satuan Pendidikan: unggah berkas Rapor Pendidikan sekolah.
 *
 * Komponen hanya mengurus unggahan, deteksi jenis berkas (murah, sekadar
 * membaca nama sheet + 15 baris pertama), dan pemantauan status. Parsing yang
 * sesungguhnya dikerjakan ProsesImporSekolah di antrean, sesuai aturan
 * "jangan memproses XLSX di dalam siklus request" pada CLAUDE.md.
 *
 * CATATAN JUJUR. Struktur berkas Rapor Pendidikan tingkat satuan pendidikan
 * belum diuji tim dengan berkas asli. Alur ini akan menolak berkas yang
 * strukturnya tidak dikenali dengan pesan spesifik, bukan memaksakannya.
 */
class UnggahBerkas extends Component
{
    use WithFileUploads;

    public $berkas;

    public ?int $imporId = null;

    public ?string $galat = null;

    public function updatedBerkas(): void
    {
        $this->galat = null;
        $this->validateOnly('berkas', ['berkas' => 'file|mimes:xlsx|max:25600']);
    }

    public function proses(): void
    {
        $this->validate(
            ['berkas' => 'required|file|mimes:xlsx|max:25600'],
            [
                'berkas.required' => 'Pilih berkas Rapor Pendidikan satuan pendidikan lebih dulu.',
                'berkas.mimes' => 'Berkas harus berformat .xlsx seperti yang diunduh dari akun belajar.id.',
                'berkas.max' => 'Ukuran berkas melebihi 25 MB.',
            ],
        );

        $path = $this->berkas->getRealPath();
        $jenis = app(DeteksiJenisBerkas::class)->untuk($path);

        if ($jenis === DeteksiJenisBerkas::DAERAH) {
            $this->galat = 'Berkas ini adalah Data Rapor Pendidikan tingkat daerah, bukan tingkat satuan '.
                'pendidikan. Impor berkas daerah dilakukan lewat jalur dinas.';

            return;
        }

        if ($jenis === DeteksiJenisBerkas::TIDAK_DIKENAL) {
            $this->galat = 'Berkas tidak dapat dikenali sebagai Rapor Pendidikan satuan pendidikan. '.
                'Pastikan berkas .xlsx diunduh langsung dari akun belajar.id sekolah Anda tanpa diubah, '.
                'dan memuat lembar capaian dengan kolom "Label Capaian".';

            return;
        }

        $hash = hash_file('sha256', $path);
        $lama = ImporBerkas::where('hash_berkas', $hash)->first();
        if ($lama !== null && $lama->status === 'selesai') {
            $this->imporId = $lama->id;
            $this->reset('berkas');

            return;
        }

        $lokasi = 'impor-sekolah/'.Str::uuid()->toString().'.xlsx';
        Storage::disk('local')->put($lokasi, file_get_contents($path));

        $impor = $lama ?? new ImporBerkas;
        $impor->fill([
            'nama_berkas' => $this->berkas->getClientOriginalName(),
            'jenis' => 'satuan',
            'hash_berkas' => $hash,
            'status' => 'antre',
            'catatan_galat' => null,
            'diunggah_oleh' => auth()->id(),
        ])->save();

        ProsesImporSekolah::dispatch($impor->id, $lokasi);

        $this->imporId = $impor->id;
        $this->reset('berkas');
    }

    public function ulangi(): void
    {
        $this->imporId = null;
        $this->galat = null;
    }

    /**
     * Setelah berkas selesai diproses, arahкан ke beranda sekolah tempat
     * seluruh analisis (profil, prioritas, RKT) dapat dibuka.
     */
    public function keBeranda()
    {
        return redirect()->route('sekolah.beranda');
    }

    /**
     * Dipanggil oleh wire:poll selama impor berjalan. Begitu status berubah
     * menjadi "selesai", pengguna langsung dibawa ke beranda.
     */
    public function periksaSelesai()
    {
        if ($this->impor?->status === 'selesai') {
            return redirect()->route('sekolah.beranda');
        }

        return null;
    }

    #[Computed]
    public function impor(): ?ImporBerkas
    {
        return $this->imporId !== null ? ImporBerkas::find($this->imporId) : null;
    }

    #[Computed]
    public function wilayahSatuan()
    {
        return $this->impor?->capaian()->with('wilayah')->first()?->wilayah;
    }

    public function render()
    {
        return view('livewire.sekolah.unggah-berkas')
            ->layout('layouts::app', ['header' => 'Unggah berkas']);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Akar\Parsers;

use App\Models\Indikator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MetadataIndikatorParser
{
    private const KOLOM_WAJIB = [
        'Jenis Layanan/Jenjang Pendidikan',
        'Nomor Indikator',
        'Nama Indikator',
        'Label Merah',
        'Ketersediaan Indikator di Tingkat Kabupaten/Kota',
    ];

    /** @return array<int, array<string, mixed>> daftar atribut indikator siap simpan */
    public function parse(string $path): array
    {
        if (! is_readable($path)) {
            throw new RuntimeException("Berkas metadata tidak dapat dibaca: {$path}");
        }

        $pegangan = fopen($path, 'r');
        if ($pegangan === false) {
            throw new RuntimeException("Berkas metadata gagal dibuka: {$path}");
        }

        try {
            $header = fgetcsv($pegangan);
            if ($header === false || $header === null) {
                throw new RuntimeException('Berkas metadata kosong.');
            }
            $header = array_map(static fn ($h) => trim((string) $h, " \t\n\r\0\x0B\xEF\xBB\xBF"), $header);

            $hilang = array_diff(self::KOLOM_WAJIB, $header);
            if ($hilang !== []) {
                throw new RuntimeException(
                    'Struktur berkas metadata tidak dikenali. Kolom hilang: '.implode(', ', $hilang)
                );
            }

            $hasil = [];
            $nomorBaris = 1;
            while (($baris = fgetcsv($pegangan)) !== false) {
                $nomorBaris++;
                if ($this->barisKosong($baris)) {
                    continue;
                }

                if (count($baris) !== count($header)) {
                    throw new RuntimeException(
                        "Baris {$nomorBaris} berkas metadata memiliki ".count($baris).
                        ' kolom, seharusnya '.count($header).'.'
                    );
                }

                $data = array_combine($header, array_map(static fn ($v) => trim((string) $v), $baris));
                $hasil[] = $this->normalisasiBaris($data, $nomorBaris);
            }

            return $hasil;
        } finally {
            fclose($pegangan);
        }
    }

    /** @return int jumlah indikator yang tersimpan */
    public function impor(string $path): int
    {
        $baris = $this->parse($path);

        DB::transaction(function () use ($baris) {
            foreach ($baris as $atribut) {
                Indikator::updateOrCreate(
                    [
                        'nomor' => $atribut['nomor'],
                        'jenis_layanan' => $atribut['jenis_layanan'],
                        'nama' => $atribut['nama'],
                    ],
                    $atribut,
                );
            }

            $this->petakanInduk();
        });

        return count($baris);
    }

    /**
     * @param  array<string, string>  $data
     * @return array<string, mixed>
     */
    private function normalisasiBaris(array $data, int $nomorBaris): array
    {
        $nomor = $data['Nomor Indikator'];
        if ($nomor === '') {
            throw new RuntimeException("Baris {$nomorBaris} berkas metadata tidak memiliki Nomor Indikator.");
        }

        $dimensi = strtoupper(substr($nomor, 0, 1));
        if (! in_array($dimensi, ['A', 'B', 'C', 'D', 'E'], true)) {
            throw new RuntimeException(
                "Baris {$nomorBaris}: dimensi indikator '{$nomor}' di luar A-E."
            );
        }

        return [
            'nomor' => $nomor,
            'dimensi' => $dimensi,
            'nama' => $data['Nama Indikator'],
            'jenis_layanan' => $data['Jenis Layanan/Jenjang Pendidikan'],
            'definisi_konseptual' => $this->atauNull($data['Definisi Konseptual'] ?? ''),
            'definisi_operasional' => $this->atauNull($data['Definisi Operasional Daerah'] ?? ''),
            'sumber_data' => $this->atauNull($data['Sumber Data'] ?? ''),
            'label_merah' => $this->atauNull($data['Label Merah'] ?? ''),
            'definisi_merah' => $this->atauNull($data['Definisi Label Merah'] ?? ''),
            'label_kuning' => $this->atauNull($data['Label Kuning'] ?? ''),
            'definisi_kuning' => $this->atauNull($data['Definisi Label Kuning'] ?? ''),
            'label_hijau' => $this->atauNull($data['Label Hijau'] ?? ''),
            'definisi_hijau' => $this->atauNull($data['Definisi Label Hijau'] ?? ''),
            'tersedia_satuan' => $this->keBoolean($data['Ketersediaan Indikator di Tingkat Satuan Pendidikan'] ?? ''),
            'tersedia_kabkota' => $this->keBoolean($data['Ketersediaan Indikator di Tingkat Kabupaten/Kota'] ?? ''),
            'tersedia_provinsi' => $this->keBoolean($data['Ketersediaan Indikator di Tingkat Provinsi'] ?? ''),
        ];
    }

    private function petakanInduk(): void
    {
        $indikator = Indikator::query()->get(['id', 'nomor', 'jenis_layanan']);

        $peta = [];
        foreach ($indikator as $satu) {
            $peta[$satu->jenis_layanan][$satu->nomor][] = $satu->id;
        }

        foreach ($indikator as $satu) {
            $nomorInduk = $this->nomorInduk($satu->nomor);
            if ($nomorInduk === null) {
                continue;
            }

            $kandidat = $peta[$satu->jenis_layanan][$nomorInduk] ?? [];
            if (count($kandidat) === 1) {
                Indikator::whereKey($satu->id)->update(['induk_id' => $kandidat[0]]);
            }
        }
    }

    private function nomorInduk(string $nomor): ?string
    {
        $bagian = explode('.', $nomor);
        if (count($bagian) < 2) {
            return null;
        }
        array_pop($bagian);

        return implode('.', $bagian);
    }

    /** @param  array<int, mixed>  $baris */
    private function barisKosong(array $baris): bool
    {
        foreach ($baris as $sel) {
            if (trim((string) $sel) !== '') {
                return false;
            }
        }

        return true;
    }

    private function atauNull(string $nilai): ?string
    {
        $nilai = trim($nilai);

        return ($nilai === '' || $nilai === '-') ? null : $nilai;
    }

    private function keBoolean(string $nilai): bool
    {
        return strtolower(trim($nilai)) === 'ya';
    }
}

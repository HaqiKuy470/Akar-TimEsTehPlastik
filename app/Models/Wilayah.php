<?php

namespace App\Models;

use Database\Factories\WilayahFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Wilayah administratif atau satuan pendidikan yang menjadi subjek analisis.
 *
 * Satu sheet provinsi menghasilkan banyak baris `wilayah` level `kabkota`
 * ditambah satu baris level `provinsi` (baris dengan Kabupaten/Kota bernilai
 * "-" pada berkas sumber, yang merupakan agregat provinsi dan dipakai sebagai
 * pembanding, bukan data kabupaten).
 */
class Wilayah extends Model
{
    /** @use HasFactory<WilayahFactory> */
    use HasFactory;

    protected $table = 'wilayah';

    protected $fillable = [
        'level',
        'provinsi',
        'kabupaten_kota',
        'nama_satuan',
        'induk_id',
    ];

    public function induk(): BelongsTo
    {
        return $this->belongsTo(self::class, 'induk_id');
    }

    public function anak(): HasMany
    {
        return $this->hasMany(self::class, 'induk_id');
    }

    public function capaian(): HasMany
    {
        return $this->hasMany(Capaian::class, 'wilayah_id');
    }

    /**
     * Nama wilayah yang layak ditampilkan ke pengguna, misalnya
     * "Kabupaten Bangkalan" atau "Provinsi Jawa Timur".
     */
    public function namaTampilan(): string
    {
        return match ($this->level) {
            'nasional' => 'Nasional',
            'provinsi' => 'Provinsi '.$this->provinsi,
            'kabkota' => (string) $this->kabupaten_kota,
            'satuan' => (string) $this->nama_satuan,
            default => trim(($this->kabupaten_kota ?? '').' '.($this->provinsi ?? '')),
        };
    }
}

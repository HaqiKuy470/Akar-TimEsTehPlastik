<?php

namespace App\Models;

use Database\Factories\CapaianFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu nilai capaian: label satu indikator untuk satu wilayah, tahun, jenjang,
 * dan status satuan pendidikan tertentu.
 *
 * Tabel ini adalah tabel terbesar dalam sistem. Setiap baris sheet provinsi
 * menghasilkan hingga ratusan baris `capaian` (satu per indikator).
 */
class Capaian extends Model
{
    /** @use HasFactory<CapaianFactory> */
    use HasFactory;

    protected $table = 'capaian';

    protected $fillable = [
        'impor_id',
        'wilayah_id',
        'indikator_id',
        'tahun',
        'jenis_satuan',
        'status_satuan',
        'label_capaian',
        'perubahan_nilai',
    ];

    protected $casts = [
        'tahun' => 'integer',
    ];

    public function impor(): BelongsTo
    {
        return $this->belongsTo(ImporBerkas::class, 'impor_id');
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(Indikator::class, 'indikator_id');
    }

    /**
     * Capaian yang bermasalah adalah yang berlabel Kurang atau Sedang.
     * Nilai "Tidak Tersedia" bukan masalah, melainkan ketiadaan data.
     */
    public function scopeBermasalah($query)
    {
        return $query->whereIn('label_capaian', ['Kurang', 'Sedang']);
    }
}

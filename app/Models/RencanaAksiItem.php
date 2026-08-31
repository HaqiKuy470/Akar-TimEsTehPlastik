<?php

namespace App\Models;

use Database\Factories\RencanaAksiItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris rencana tindak lanjut: masalah, akar masalah, kegiatan usulan,
 * penanggung jawab, indikator keberhasilan, dan perkiraan waktu.
 */
class RencanaAksiItem extends Model
{
    /** @use HasFactory<RencanaAksiItemFactory> */
    use HasFactory;

    protected $table = 'rencana_aksi_item';

    protected $fillable = [
        'rencana_aksi_id',
        'masalah',
        'akar_masalah',
        'kegiatan',
        'penanggung_jawab',
        'indikator_keberhasilan',
        'perkiraan_waktu',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function rencanaAksi(): BelongsTo
    {
        return $this->belongsTo(RencanaAksi::class, 'rencana_aksi_id');
    }
}

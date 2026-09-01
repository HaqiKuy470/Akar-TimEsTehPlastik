<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RencanaAksiItem extends Model
{
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

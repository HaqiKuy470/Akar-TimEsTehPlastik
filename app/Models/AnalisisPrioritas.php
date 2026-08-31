<?php

namespace App\Models;

use Database\Factories\AnalisisPrioritasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Skor prioritas satu indikator bermasalah dalam sebuah analisis.
 *
 * Kolom `komponen_skor` menyimpan rincian tiap komponen pembentuk skor
 * (label, perubahan, posisi relatif, dampak turunan) supaya skor dapat
 * ditelusuri, bukan sekadar satu angka.
 */
class AnalisisPrioritas extends Model
{
    /** @use HasFactory<AnalisisPrioritasFactory> */
    use HasFactory;

    protected $table = 'analisis_prioritas';

    protected $fillable = [
        'analisis_id',
        'indikator_id',
        'skor',
        'komponen_skor',
        'kalimat_penjelas',
        'peringkat',
    ];

    protected $casts = [
        'skor' => 'decimal:2',
        'komponen_skor' => 'array',
        'peringkat' => 'integer',
    ];

    public function analisis(): BelongsTo
    {
        return $this->belongsTo(Analisis::class, 'analisis_id');
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(Indikator::class, 'indikator_id');
    }

    public function akar(): HasMany
    {
        return $this->hasMany(AnalisisAkar::class, 'analisis_prioritas_id');
    }
}

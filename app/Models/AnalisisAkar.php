<?php

namespace App\Models;

use App\Enums\Keyakinan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisisAkar extends Model
{
    use HasFactory;

    protected $table = 'analisis_akar';

    protected $fillable = [
        'analisis_prioritas_id',
        'kode_akar',
        'label',
        'bukti',
        'keyakinan',
    ];

    protected $casts = [
        'bukti' => 'array',
        'keyakinan' => Keyakinan::class,
    ];

    public function prioritas(): BelongsTo
    {
        return $this->belongsTo(AnalisisPrioritas::class, 'analisis_prioritas_id');
    }
}

<?php

namespace App\Models;

use App\Enums\Keyakinan;
use Database\Factories\AnalisisAkarFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu kandidat akar masalah untuk indikator prioritas, beserta bukti
 * pendukungnya dan tingkat keyakinannya.
 *
 * Kolom `bukti` menyimpan daftar indikator pendukung yang diperiksa beserta
 * labelnya, sehingga kesimpulan dapat ditelusuri kembali ke datanya.
 */
class AnalisisAkar extends Model
{
    /** @use HasFactory<AnalisisAkarFactory> */
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

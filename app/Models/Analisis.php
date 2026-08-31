<?php

namespace App\Models;

use Database\Factories\AnalisisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Satu kali proses analisis untuk kombinasi wilayah + tahun + jenjang + status.
 *
 * Kolom `bobot_dipakai` menyimpan salinan config/akar.php saat analisis
 * dijalankan, sehingga hasil lama tetap dapat direproduksi walau konfigurasi
 * berubah kemudian.
 */
class Analisis extends Model
{
    /** @use HasFactory<AnalisisFactory> */
    use HasFactory;

    protected $table = 'analisis';

    protected $fillable = [
        'wilayah_id',
        'tahun',
        'jenis_satuan',
        'status_satuan',
        'bobot_dipakai',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bobot_dipakai' => 'array',
    ];

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function prioritas(): HasMany
    {
        return $this->hasMany(AnalisisPrioritas::class, 'analisis_id');
    }

    public function rencanaAksi(): HasMany
    {
        return $this->hasMany(RencanaAksi::class, 'analisis_id');
    }
}

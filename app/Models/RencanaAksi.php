<?php

namespace App\Models;

use Database\Factories\RencanaAksiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Draf rencana tindak lanjut yang disusun dari hasil analisis. Pengguna
 * menyunting, menambah, dan menghapus item sebelum mengekspornya.
 */
class RencanaAksi extends Model
{
    /** @use HasFactory<RencanaAksiFactory> */
    use HasFactory;

    protected $table = 'rencana_aksi';

    protected $fillable = [
        'analisis_id',
        'judul',
        'dibuat_oleh',
    ];

    public function analisis(): BelongsTo
    {
        return $this->belongsTo(Analisis::class, 'analisis_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function item(): HasMany
    {
        return $this->hasMany(RencanaAksiItem::class, 'rencana_aksi_id')->orderBy('urutan');
    }
}

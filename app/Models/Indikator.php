<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indikator extends Model
{
    protected $table = 'indikator';

    protected $fillable = [
        'nomor', 'induk_id', 'dimensi', 'nama', 'jenis_layanan',
        'definisi_konseptual', 'definisi_operasional', 'sumber_data',
        'label_merah', 'definisi_merah',
        'label_kuning', 'definisi_kuning',
        'label_hijau', 'definisi_hijau',
        'tersedia_satuan', 'tersedia_kabkota', 'tersedia_provinsi',
    ];

    protected $casts = [
        'tersedia_satuan' => 'boolean',
        'tersedia_kabkota' => 'boolean',
        'tersedia_provinsi' => 'boolean',
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
        return $this->hasMany(Capaian::class, 'indikator_id');
    }
}

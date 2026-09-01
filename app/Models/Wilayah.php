<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wilayah extends Model
{
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

<?php

namespace App\Models;

use Database\Factories\ImporBerkasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catatan satu kali impor berkas Rapor Pendidikan.
 *
 * `hash_berkas` (SHA-256 isi berkas) bersifat unik sehingga mengimpor berkas
 * yang sama dua kali tidak menghasilkan data ganda: impor kedua mengenali
 * berkas lama dan menghapus capaian sebelumnya sebelum menulis ulang.
 */
class ImporBerkas extends Model
{
    /** @use HasFactory<ImporBerkasFactory> */
    use HasFactory;

    protected $table = 'impor_berkas';

    protected $fillable = [
        'nama_berkas',
        'jenis',
        'tahun_edisi',
        'hash_berkas',
        'status',
        'jumlah_baris',
        'catatan_galat',
        'diunggah_oleh',
        'diproses_pada',
    ];

    protected $casts = [
        'tahun_edisi' => 'integer',
        'jumlah_baris' => 'integer',
        'diproses_pada' => 'datetime',
    ];

    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }

    public function capaian(): HasMany
    {
        return $this->hasMany(Capaian::class, 'impor_id');
    }
}

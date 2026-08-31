<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('impor_id')->constrained('impor_berkas')->cascadeOnDelete();
            $table->foreignId('wilayah_id')->constrained('wilayah')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('indikator')->cascadeOnDelete();
            $table->smallInteger('tahun');
            $table->string('jenis_satuan', 64);
            $table->string('status_satuan', 32);
            $table->enum('label_capaian', ['Baik', 'Sedang', 'Kurang', 'Tidak Tersedia']);
            $table->enum('perubahan_nilai', ['Naik', 'Turun', 'Tidak berubah', 'Tidak Tersedia']);
            $table->timestamps();

            $table->index(['wilayah_id', 'tahun', 'jenis_satuan', 'status_satuan', 'indikator_id'], 'capaian_lookup');
            $table->index(['indikator_id', 'tahun', 'jenis_satuan', 'status_satuan', 'label_capaian'], 'capaian_banding');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capaian');
    }
};

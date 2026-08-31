<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rencana_aksi_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rencana_aksi_id')->constrained('rencana_aksi')->cascadeOnDelete();
            $table->string('masalah');
            $table->string('akar_masalah');
            $table->text('kegiatan');
            $table->string('penanggung_jawab', 128)->nullable();
            $table->text('indikator_keberhasilan')->nullable();
            $table->string('perkiraan_waktu', 64)->nullable();
            $table->smallInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rencana_aksi_item');
    }
};

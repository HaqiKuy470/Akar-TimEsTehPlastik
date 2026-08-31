<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_akar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_prioritas_id')->constrained('analisis_prioritas')->cascadeOnDelete();
            $table->string('kode_akar', 64);
            $table->string('label');
            $table->json('bukti');                          // indikator pendukung + labelnya
            $table->enum('keyakinan', ['kuat', 'sedang', 'lemah', 'tidak_cukup_bukti']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_akar');
    }
};

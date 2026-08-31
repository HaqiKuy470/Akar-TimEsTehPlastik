<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_prioritas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('indikator')->cascadeOnDelete();
            $table->decimal('skor', 5, 2);
            $table->json('komponen_skor');                  // rincian tiap komponen skor
            $table->text('kalimat_penjelas')->nullable();
            $table->smallInteger('peringkat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_prioritas');
    }
};

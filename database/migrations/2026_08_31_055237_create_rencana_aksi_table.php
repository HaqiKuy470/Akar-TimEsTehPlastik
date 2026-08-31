<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rencana_aksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->string('judul');
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rencana_aksi');
    }
};

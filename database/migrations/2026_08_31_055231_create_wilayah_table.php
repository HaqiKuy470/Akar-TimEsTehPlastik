<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id();
            $table->enum('level', ['nasional', 'provinsi', 'kabkota', 'satuan']);
            $table->string('provinsi', 64)->nullable();
            $table->string('kabupaten_kota', 96)->nullable();
            $table->string('nama_satuan', 160)->nullable();   // untuk mode satuan pendidikan
            $table->foreignId('induk_id')->nullable()->constrained('wilayah')->nullOnDelete();
            $table->timestamps();

            $table->unique(['level', 'provinsi', 'kabupaten_kota', 'nama_satuan'], 'wilayah_identitas_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah');
    }
};

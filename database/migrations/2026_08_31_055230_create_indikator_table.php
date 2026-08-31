<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indikator', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 16);                 // 'A.1', 'A.1.1', 'A.1.skor'
            $table->foreignId('induk_id')->nullable()->constrained('indikator')->nullOnDelete();
            $table->char('dimensi', 1);                   // 'A'..'E'
            $table->string('nama');
            $table->string('jenis_layanan', 128);
            $table->text('definisi_konseptual')->nullable();
            $table->text('definisi_operasional')->nullable();
            $table->string('sumber_data', 191)->nullable();
            $table->string('label_merah', 191)->nullable();
            $table->text('definisi_merah')->nullable();
            $table->string('label_kuning', 191)->nullable();
            $table->text('definisi_kuning')->nullable();
            $table->string('label_hijau', 191)->nullable();
            $table->text('definisi_hijau')->nullable();
            $table->boolean('tersedia_satuan')->default(false);
            $table->boolean('tersedia_kabkota')->default(false);
            $table->boolean('tersedia_provinsi')->default(false);
            $table->timestamps();

            // Berkas Metadata resmi memuat beberapa baris dengan nomor sama pada
            // jenis layanan sama namun indikator berbeda (mis. B.10). Karena itu
            // kunci unik mengikutkan nama, bukan hanya (nomor, jenis_layanan).
            $table->unique(['nomor', 'jenis_layanan', 'nama'], 'indikator_identitas_unik');
            $table->index(['nomor', 'jenis_layanan']);
            $table->index('dimensi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indikator');
    }
};

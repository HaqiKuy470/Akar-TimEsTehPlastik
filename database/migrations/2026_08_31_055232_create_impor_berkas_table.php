<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impor_berkas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_berkas');
            $table->enum('jenis', ['daerah', 'satuan', 'metadata']);
            $table->smallInteger('tahun_edisi')->nullable();
            $table->char('hash_berkas', 64)->unique();       // cegah impor ganda
            $table->enum('status', ['antre', 'proses', 'selesai', 'gagal'])->default('antre');
            $table->integer('jumlah_baris')->default(0);
            $table->text('catatan_galat')->nullable();
            $table->foreignId('diunggah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diproses_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impor_berkas');
    }
};

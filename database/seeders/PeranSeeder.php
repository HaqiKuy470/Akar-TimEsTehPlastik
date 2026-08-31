<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Membuat tiga peran AKAR beserta izinnya, sesuai PRD F9.
 *
 * | Peran          | Pengguna                    | Izin                                        |
 * |----------------|-----------------------------|---------------------------------------------|
 * | admin          | Pengelola sistem            | seluruh izin (termasuk impor berkas daerah) |
 * | analis_dinas   | Analis Perencanaan Dinas    | jalankan analisis, kelola rencana aksi      |
 * | kepala_sekolah | Kepala satuan pendidikan    | unggah berkas satuan, analisis, rencana     |
 *
 * Aman dijalankan berkali-kali: peran dan izin dibuat dengan firstOrCreate.
 */
class PeranSeeder extends Seeder
{
    /**
     * Seluruh izin yang dikenal sistem beserta keterangannya.
     */
    public const IZIN = [
        'impor.daerah' => 'Mengimpor berkas Rapor Pendidikan level daerah',
        'analisis.jalankan' => 'Menjalankan analisis prioritas dan akar masalah',
        'rencana.kelola' => 'Menyusun dan menyunting rencana tindak lanjut',
        'berkas.satuan.unggah' => 'Mengunggah berkas Rapor Pendidikan satuan pendidikan',
    ];

    /**
     * Pemetaan peran ke daftar izinnya.
     */
    public const PETA_PERAN = [
        'admin' => ['impor.daerah', 'analisis.jalankan', 'rencana.kelola', 'berkas.satuan.unggah'],
        'analis_dinas' => ['analisis.jalankan', 'rencana.kelola'],
        'kepala_sekolah' => ['berkas.satuan.unggah', 'analisis.jalankan', 'rencana.kelola'],
    ];

    public function run(): void
    {
        // Bersihkan cache izin lebih dulu. Tanpa ini, sisa cache dari proses
        // sebelumnya (mis. setelah migrate:fresh) membuat syncPermissions
        // membaca daftar izin yang sudah usang.
        App::make(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        foreach (self::IZIN as $nama => $keterangan) {
            Permission::findOrCreate($nama, $guard);
        }

        // Segarkan cache setelah izin dibuat. DatabaseSeeder memakai trait
        // WithoutModelEvents yang membungkam event `saved`, sehingga spatie
        // tidak otomatis menyegarkan cache dan syncPermissions di bawah akan
        // gagal menemukan izin yang baru saja dibuat.
        App::make(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PETA_PERAN as $namaPeran => $daftarIzin) {
            $peran = Role::findOrCreate($namaPeran, $guard);
            $peran->syncPermissions($daftarIzin);
        }

        // Segarkan cache izin agar peran baru langsung berlaku.
        App::make(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

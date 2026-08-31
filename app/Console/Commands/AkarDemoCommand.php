<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

/**
 * Menyiapkan basis data siap-demo dalam satu perintah: peran, akun demo,
 * indikator, dan satu sheet provinsi contoh (bila berkas dataset tersedia).
 *
 * Dipakai anggota tim dan penilai untuk mencoba aplikasi tanpa menjalankan
 * seluruh alur impor. Untuk pengisian data sebenarnya, lihat `akar:impor`
 * dan ARCHITECTURE.md bagian 4.1.
 */
class AkarDemoCommand extends Command
{
    protected $signature = 'akar:demo {--fresh : Jalankan migrate:fresh lebih dulu (menghapus seluruh data)}';

    protected $description = 'Siapkan basis data siap-demo (peran, akun demo, indikator, satu provinsi contoh)';

    public function handle(): int
    {
        if (app()->isProduction() && ! $this->confirm(
            'APP_ENV=production. Perintah ini menulis data demo ke basis data produksi. Lanjutkan?',
            false,
        )) {
            $this->components->warn('Dibatalkan.');

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->components->info('Menyegarkan skema basis data...');
            $this->call('migrate:fresh', ['--force' => true]);
        } else {
            $this->call('migrate', ['--force' => true]);
        }

        $this->components->info('Mengisi data demo...');
        $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);

        $this->newLine();
        $this->components->info('Basis data demo siap. Masuk dengan admin@akar.test / password.');

        return self::SUCCESS;
    }
}

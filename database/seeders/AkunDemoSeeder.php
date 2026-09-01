<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Membuat satu akun demo untuk tiap peran, dipakai penilai kompetisi.
 * Kredensialnya dicantumkan di README.
 *
 * Bergantung pada PeranSeeder; pastikan peran sudah ada sebelum seeder ini
 * dijalankan (lihat DatabaseSeeder).
 */
class AkunDemoSeeder extends Seeder
{
    /**
     * Daftar akun demo: email => [nama, peran].
     */
    public const AKUN = [
        'superadmin@akar.test' => ['Super Admin', 'superadmin'],
        'admin@akar.test' => ['Admin AKAR', 'admin'],
        'analis@akar.test' => ['Analis Dinas Pendidikan', 'analis_dinas'],
        'kepala@akar.test' => ['Kepala SMP Negeri', 'kepala_sekolah'],
    ];

    public const KATA_SANDI = 'password';

    public function run(): void
    {
        foreach (self::AKUN as $email => [$nama, $peran]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $nama,
                    'password' => Hash::make(self::KATA_SANDI),
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$peran]);
        }
    }
}

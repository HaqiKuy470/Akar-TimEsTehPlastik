<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Membuat akun awal:
 *  - satu akun demo per peran kerja (admin/analis/kepala sekolah), untuk
 *    penilai kompetisi; kredensialnya di README.
 *  - satu akun super admin dari .env (SUPERADMIN_EMAIL / SUPERADMIN_PASSWORD),
 *    supaya kredensial pembuat akun tidak tersimpan di repositori. Dilewati
 *    bila kedua nilai .env kosong.
 *
 * Bergantung pada PeranSeeder; pastikan peran sudah ada sebelum seeder ini
 * dijalankan (lihat DatabaseSeeder).
 */
class AkunDemoSeeder extends Seeder
{
    /**
     * Akun demo peran kerja: email => [nama, peran].
     */
    public const AKUN = [
        'admin@akar.test' => ['Admin AKAR', 'admin'],
        'analis@akar.test' => ['Analis Dinas Pendidikan', 'analis_dinas'],
        'kepala@akar.test' => ['Kepala SMP Negeri', 'kepala_sekolah'],
    ];

    public const KATA_SANDI = 'password';

    public function run(): void
    {
        foreach (self::AKUN as $email => [$nama, $peran]) {
            $this->buatAkun($email, $nama, self::KATA_SANDI, $peran);
        }

        $super = (array) config('akar.superadmin');
        if (! empty($super['email']) && ! empty($super['kata_sandi'])) {
            $this->buatAkun(
                $super['email'],
                $super['nama'] ?? 'Super Admin',
                $super['kata_sandi'],
                'superadmin',
            );
        }
    }

    private function buatAkun(string $email, string $nama, string $kataSandi, string $peran): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $nama,
                'password' => Hash::make($kataSandi),
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles([$peran]);
    }
}

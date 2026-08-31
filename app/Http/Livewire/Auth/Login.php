<?php

declare(strict_types=1);

namespace App\Http\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * F9 — halaman masuk.
 *
 * AKAR belum memakai Fortify atau Breeze; kebutuhan autentikasinya sederhana
 * (tiga akun peran, tanpa pendaftaran mandiri), jadi login ditangani langsung
 * di komponen ini. Tidak ada logika domain di sini.
 */
#[Layout('layouts.tamu')]
#[Title('Masuk')]
class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public bool $ingatSaya = false;

    public function login(): void
    {
        $this->validate();

        $this->pastikanTidakDibatasi();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->ingatSaya)) {
            RateLimiter::hit($this->kunciPembatas());

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        RateLimiter::clear($this->kunciPembatas());
        session()->regenerate();

        $beranda = Auth::user()->hasRole('kepala_sekolah')
            ? route('sekolah.beranda')
            : route('dinas.profil');

        $this->redirectIntended($beranda, navigate: true);
    }

    /**
     * Batasi lima percobaan gagal per menit untuk tiap pasangan email + IP,
     * mengikuti pola bawaan Laravel. Ini mencegah penebakan kata sandi tanpa
     * mengganggu pengguna sah yang salah ketik sesekali.
     */
    private function pastikanTidakDibatasi(): void
    {
        if (! RateLimiter::tooManyAttempts($this->kunciPembatas(), maxAttempts: 5)) {
            return;
        }

        $detik = RateLimiter::availableIn($this->kunciPembatas());

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$detik} detik.",
        ]);
    }

    private function kunciPembatas(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Livewire\Superadmin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Kelola akun')]
class KelolaAkun extends Component
{
    public const PERAN_DIIZINKAN = [
        'admin' => 'Administrator (kementerian)',
        'analis_dinas' => 'Analis Dinas Pendidikan',
        'kepala_sekolah' => 'Kepala sekolah',
    ];

    public string $nama = '';

    public string $email = '';

    public string $peran = 'analis_dinas';

    public string $kataSandi = '';

    public ?int $hapusId = null;

    public function buatAkun(): void
    {
        $data = $this->validate([
            'nama' => 'required|string|min:3|max:120',
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'peran' => ['required', Rule::in(array_keys(self::PERAN_DIIZINKAN))],
            'kataSandi' => 'required|string|min:8|max:120',
        ], attributes: [
            'kataSandi' => 'kata sandi',
        ]);

        $user = User::create([
            'name' => $data['nama'],
            'email' => $data['email'],
            'password' => Hash::make($data['kataSandi']),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($data['peran']);

        $this->reset(['nama', 'email', 'kataSandi']);
        $this->peran = 'analis_dinas';

        session()->flash('sukses', "Akun {$user->email} dibuat sebagai ".self::PERAN_DIIZINKAN[$data['peran']].'.');
    }

    public function konfirmasiHapus(int $id): void
    {
        $this->hapusId = $id;
    }

    public function batalHapus(): void
    {
        $this->hapusId = null;
    }

    public function hapusAkun(): void
    {
        if ($this->hapusId === null) {
            return;
        }

        $user = User::find($this->hapusId);

        if ($user !== null
            && $user->id !== auth()->id()
            && ! $user->hasRole('superadmin')) {
            $email = $user->email;
            $user->delete();
            session()->flash('sukses', "Akun {$email} dihapus.");
        }

        $this->hapusId = null;
    }

    public function render()
    {
        $daftar = User::query()
            ->with('roles:id,name')
            ->orderByDesc('id')
            ->get(['id', 'name', 'email', 'created_at']);

        $labelPeran = array_merge(self::PERAN_DIIZINKAN, ['superadmin' => 'Super admin']);

        return view('livewire.superadmin.kelola-akun', [
            'daftar' => $daftar,
            'labelPeran' => $labelPeran,
            'jumlahPeran' => Role::query()->withCount('users')->pluck('users_count', 'name'),
        ])->layout('layouts.app', ['header' => 'Kelola akun']);
    }
}

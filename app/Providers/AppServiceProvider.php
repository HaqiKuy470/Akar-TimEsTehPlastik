<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Seluruh teks tanggal yang dilihat pengguna berbahasa Indonesia.
        Carbon::setLocale('id');
        CarbonImmutable::setLocale('id');
    }
}

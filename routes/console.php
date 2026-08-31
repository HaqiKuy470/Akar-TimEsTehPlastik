<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Antrean lewat cron (ARCHITECTURE.md bagian 4.2).
|
| Lingkungan cPanel tidak menyediakan Supervisor, jadi tidak ada worker yang
| berjalan terus-menerus. Sebagai gantinya cron memanggil `schedule:run` setiap
| menit, dan jadwal di bawah menjalankan queue:work sampai antrean kosong.
|
|   --stop-when-empty : proses berhenti setelah tidak ada job tersisa
|   --max-time=50     : berhenti sebelum cron menit berikutnya menumpuk
|   withoutOverlapping: lapis pengaman kedua terhadap tumpang tindih
|
| Cron di cPanel:
|   * * * * * cd /home/USER/akar && php artisan schedule:run >> /dev/null 2>&1
*/
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=2')
    ->everyMinute()
    ->withoutOverlapping();

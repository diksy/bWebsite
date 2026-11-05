<?php

use Illuminate\Support\Facades\Schedule;

/*
| Satu entri cron cPanel cukup (tiap menit):
|   * * * * * cd /home/USER/folder-aplikasi && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
| Antrean database diproses di bawah ini (selesai sendiri bila kosong), jadi tidak perlu daemon queue:work.
*/
Schedule::command('queue:work database --stop-when-empty --max-time=50 --timeout=120 --tries=2')
    ->everyMinute()->withoutOverlapping(5)->runInBackground();

Schedule::command('artikel:terbitkan-terjadwal')->everyMinute()->withoutOverlapping(5);
Schedule::command('tema:jalankan')->hourly()->withoutOverlapping(30);
Schedule::command('log-ai:bersihkan')->daily();
Schedule::command('queue:prune-failed --hours=720')->daily();

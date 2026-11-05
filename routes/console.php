<?php

use Illuminate\Support\Facades\Schedule;

// Dijalankan cron cPanel tiap menit: php artisan schedule:run
Schedule::command('artikel:terbitkan-terjadwal')->everyMinute()->withoutOverlapping(5);

<?php

namespace App\Console\Commands;

use App\Models\AiLog;
use App\Models\AiRun;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('log-ai:bersihkan {--hari=180 : Hapus log lebih lama dari ini}')]
#[Description('Hapus log AI dan proses gagal yang sudah lama agar database tidak membengkak')]
class BersihkanLogAi extends Command
{
    public function handle(): int
    {
        $batas = now()->subDays(max(1, (int) $this->option('hari')));

        $log = AiLog::query()->where('created_at', '<', $batas)->delete();
        $run = AiRun::query()->where('created_at', '<', $batas)->whereIn('status', [AiRun::SELESAI, AiRun::GAGAL])->delete();

        $this->info("{$log} log dan {$run} proses dihapus.");

        return self::SUCCESS;
    }
}

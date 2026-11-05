<?php

namespace App\Jobs;

use App\Models\Artikel;
use App\Services\Seo\IndexNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class KirimIndexNow implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [60, 300];
    }

    public function __construct(public int $artikelId) {}

    public function handle(IndexNow $indexNow): void
    {
        $artikel = Artikel::query()->diterbitkan()->find($this->artikelId);
        if ($artikel === null) {
            return;
        }

        $indexNow->kirim([$artikel->url(), url('/')]);
    }
}

<?php

namespace App\Jobs\Ai;

use App\Models\AiRun;
use App\Services\Ai\AiResponse;
use App\Services\Ai\Pipeline\PipelineException;
use App\Services\Search\SearchManager;

class CariWeb extends TahapJob
{
    protected function tahap(): string
    {
        return 'cari_web';
    }

    protected function jalankan(AiRun $run): ?AiResponse
    {
        $hasil = app(SearchManager::class)->provider()->cari((string) $run->topik.' '.now()->year, 5);

        $sumber = collect($hasil)
            ->filter(fn ($s) => preg_match('#^https?://#i', $s['url']) === 1)
            ->map(fn ($s) => ['judul' => mb_substr($s['judul'], 0, 160), 'url' => $s['url'], 'isi' => mb_substr($s['isi'], 0, 2000)])
            ->values()->all();

        if ($sumber === []) {
            throw new PipelineException('Pencarian web tidak menemukan sumber.');
        }

        $run->simpanData('sumber', $sumber);

        return null;
    }
}

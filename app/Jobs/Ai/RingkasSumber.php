<?php

namespace App\Jobs\Ai;

use App\Models\AiRun;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiResponse;
use App\Services\Ai\Pipeline\Prompt;

class RingkasSumber extends TahapJob
{
    protected function tahap(): string
    {
        return 'ringkas';
    }

    protected function jalankan(AiRun $run): ?AiResponse
    {
        $resp = app(AiClient::class)->chat(Prompt::ringkas((string) $run->topik, $run->data['sumber'] ?? []), 0.2);
        $run->simpanData('ringkasan', $resp->teks);

        return $resp;
    }
}

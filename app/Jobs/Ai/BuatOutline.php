<?php

namespace App\Jobs\Ai;

use App\Models\AiRun;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiResponse;
use App\Services\Ai\Pipeline\Json;
use App\Services\Ai\Pipeline\PipelineException;
use App\Services\Ai\Pipeline\Prompt;

class BuatOutline extends TahapJob
{
    protected function tahap(): string
    {
        return 'outline';
    }

    protected function jalankan(AiRun $run): ?AiResponse
    {
        $tema = $run->tema ?? throw new PipelineException('Tema sudah dihapus.');
        $judulAda = $tema->artikel()->latest()->limit(20)->pluck('judul')->all();

        $resp = app(AiClient::class)->chat(Prompt::outline($tema->nama, (string) $run->topik, (string) ($run->data['ringkasan'] ?? ''), $judulAda), 0.5);
        $o = Json::urai($resp->teks);

        $bagian = array_values(array_filter(array_map(fn ($b) => is_string($b) ? trim($b) : '', (array) ($o['bagian'] ?? []))));
        if (! is_string($o['judul'] ?? null) || trim($o['judul']) === '' || count($bagian) < 2) {
            throw new PipelineException('Outline dari AI tidak lengkap.');
        }

        $run->simpanData('outline', [
            'judul' => mb_substr(trim($o['judul']), 0, 160),
            'kata_kunci_utama' => mb_substr(trim((string) ($o['kata_kunci_utama'] ?? $run->topik)), 0, 120),
            'bagian' => array_slice($bagian, 0, 8),
        ]);

        return $resp;
    }
}

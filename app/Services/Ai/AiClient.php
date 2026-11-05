<?php

namespace App\Services\Ai;

use App\Support\Settings;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Klien chat-completions kompatibel OpenAI (base URL + API key + model dari Pengaturan AI).
 */
class AiClient
{
    public function tersedia(): bool
    {
        return Settings::has('ai_base_url') && Settings::has('ai_api_key') && Settings::has('ai_model');
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $pesan
     */
    public function chat(array $pesan, float $suhu = 0.4, int $timeout = 90): AiResponse
    {
        if (! $this->tersedia()) {
            throw new RuntimeException('Pengaturan AI belum lengkap.');
        }

        $resp = Http::withToken((string) Settings::get('ai_api_key'))
            ->acceptJson()
            ->timeout($timeout)
            ->post(rtrim((string) Settings::get('ai_base_url'), '/').'/chat/completions', [
                'model' => Settings::get('ai_model'),
                'messages' => $pesan,
                'temperature' => $suhu,
            ]);

        if (! $resp->successful()) {
            $detail = $resp->json('error.message') ?? $resp->json('message') ?? mb_substr($resp->body(), 0, 200);
            throw new RuntimeException("API model membalas {$resp->status()}: {$detail}");
        }

        $teks = $resp->json('choices.0.message.content');
        if (! is_string($teks) || $teks === '') {
            throw new RuntimeException('API model tidak mengembalikan teks.');
        }

        return new AiResponse(
            $teks,
            (int) $resp->json('usage.prompt_tokens', 0),
            (int) $resp->json('usage.completion_tokens', 0),
        );
    }

    /** Uji koneksi: permintaan kecil; kembalikan ringkasan hasil. */
    public function uji(): string
    {
        $r = $this->chat([['role' => 'user', 'content' => 'Balas dengan satu kata: OK']], 0, 30);

        return 'Terhubung ke model '.Settings::get('ai_model').' ('.$r->totalToken().' token).';
    }
}

<?php

namespace App\Services\Search;

use App\Support\Settings;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TavilyProvider implements SearchProvider
{
    public function tersedia(): bool
    {
        return Settings::has('search_api_key');
    }

    public function cari(string $kueri, int $maks = 5): array
    {
        if (! $this->tersedia()) {
            throw new RuntimeException('API key pencarian web belum diisi.');
        }

        $resp = Http::withToken((string) Settings::get('search_api_key'))
            ->acceptJson()->timeout(30)
            ->post('https://api.tavily.com/search', [
                'query' => $kueri,
                'max_results' => $maks,
                'search_depth' => 'basic',
            ]);

        if (! $resp->successful()) {
            throw new RuntimeException("Tavily membalas {$resp->status()}.");
        }

        return collect($resp->json('results', []))
            ->map(fn (array $r) => ['judul' => (string) ($r['title'] ?? ''), 'url' => (string) ($r['url'] ?? ''), 'isi' => (string) ($r['content'] ?? '')])
            ->filter(fn (array $r) => $r['url'] !== '')
            ->values()
            ->all();
    }
}

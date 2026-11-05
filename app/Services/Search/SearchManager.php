<?php

namespace App\Services\Search;

use App\Support\Settings;
use InvalidArgumentException;

/**
 * Memilih adapter pencarian web berdasarkan pengaturan (default: Tavily).
 */
class SearchManager
{
    /** @var array<string, class-string<SearchProvider>> */
    public const ADAPTER = ['tavily' => TavilyProvider::class];

    public function provider(): SearchProvider
    {
        $nama = Settings::get('search_provider', 'tavily');
        $kelas = self::ADAPTER[$nama] ?? throw new InvalidArgumentException("Adapter pencarian tidak dikenal: {$nama}");

        return app($kelas);
    }
}

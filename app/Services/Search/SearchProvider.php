<?php

namespace App\Services\Search;

interface SearchProvider
{
    /**
     * @return array<int, array{judul: string, url: string, isi: string}>
     */
    public function cari(string $kueri, int $maks = 5): array;

    public function tersedia(): bool;
}

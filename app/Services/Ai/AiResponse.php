<?php

namespace App\Services\Ai;

final readonly class AiResponse
{
    public function __construct(
        public string $teks,
        public int $tokenMasuk = 0,
        public int $tokenKeluar = 0,
    ) {}

    public function totalToken(): int
    {
        return $this->tokenMasuk + $this->tokenKeluar;
    }
}

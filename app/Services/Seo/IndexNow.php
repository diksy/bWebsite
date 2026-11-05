<?php

namespace App\Services\Seo;

use App\Support\Settings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Notifikasi IndexNow (Bing, Yandex, dll.) saat artikel terbit.
 */
class IndexNow
{
    public const ENDPOINT = 'https://api.indexnow.org/IndexNow';

    public function aktif(): bool
    {
        $nilai = Settings::get('indexnow_aktif');

        return ($nilai ?? (app()->isProduction() ? '1' : '0')) === '1';
    }

    /** Kunci dibuat otomatis sekali; file kunci dilayani di /{kunci}.txt */
    public function kunci(): string
    {
        $kunci = Settings::get('indexnow_key');
        if (! is_string($kunci) || ! preg_match('/^[a-f0-9]{32}$/', $kunci)) {
            $kunci = Str::lower(bin2hex(random_bytes(16)));
            Settings::set('indexnow_key', $kunci);
        }

        return $kunci;
    }

    /** @param  array<int, string>  $urls */
    public function kirim(array $urls): void
    {
        $urls = array_values(array_unique(array_filter($urls)));
        if ($urls === [] || ! $this->aktif()) {
            return;
        }

        $kunci = $this->kunci();
        $resp = Http::timeout(10)->acceptJson()->post(self::ENDPOINT, [
            'host' => parse_url(url('/'), PHP_URL_HOST),
            'key' => $kunci,
            'keyLocation' => url($kunci.'.txt'),
            'urlList' => $urls,
        ]);

        // 200/202 = diterima. 4xx/5xx: lempar agar job dicoba ulang / tercatat.
        if (! in_array($resp->status(), [200, 202], true)) {
            throw new RuntimeException("IndexNow membalas {$resp->status()}.");
        }
    }
}

<?php

namespace App\Services\Gaya;

/** Memeriksa CSS unggahan; mengembalikan daftar alasan penolakan (kosong = aman). */
class SanitasiCss
{
    public const MAKS_BYTE = 102400;

    /** @return list<string> */
    public function periksa(string $css): array
    {
        $galat = [];

        if (strlen($css) > self::MAKS_BYTE) {
            return ['Ukuran berkas melebihi 100 KB.'];
        }
        if (! mb_check_encoding($css, 'UTF-8') || str_contains($css, "\0")) {
            return ['Berkas harus berupa teks UTF-8.'];
        }

        $bersih = preg_replace('~/\*.*?\*/~s', '', $css) ?? '';

        $larangan = [
            '/@import/i' => '@import tidak diizinkan.',
            '/@charset/i' => '@charset tidak diizinkan.',
            '/url\s*\(/i' => 'url() tidak diizinkan (hanya font sistem; tanpa gambar/berkas eksternal).',
            '/image-set\s*\(|src\s*\(/i' => 'image-set()/src() tidak diizinkan.',
            '/expression\s*\(/i' => 'expression() tidak diizinkan.',
            '/javascript\s*:|vbscript\s*:|behavior\s*:|-moz-binding/i' => 'Properti/skema skrip tidak diizinkan.',
            '/\\\\/' => 'Karakter backslash (escape CSS) tidak diizinkan.',
            '/</' => 'Karakter "<" (HTML/script) tidak diizinkan.',
        ];
        foreach ($larangan as $pola => $pesan) {
            if (preg_match($pola, $bersih)) {
                $galat[] = $pesan;
            }
        }

        if (trim($bersih) === '') {
            $galat[] = 'Berkas CSS kosong.';
        }

        return $galat;
    }
}

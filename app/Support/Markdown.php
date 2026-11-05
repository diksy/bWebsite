<?php

namespace App\Support;

use Illuminate\Support\Str;

final class Markdown
{
    /** Render Markdown → HTML aman (HTML mentah dibuang, tautan berbahaya diblokir); gambar lazy-load. */
    public static function render(string $teks): string
    {
        $html = Str::markdown($teks, ['html_input' => 'strip', 'allow_unsafe_links' => false]);

        return preg_replace('/<img(?![^>]*\bloading=)/i', '<img loading="lazy" decoding="async"', $html) ?? $html;
    }
}

<?php

namespace App\Support;

/** Identitas situs publik. */
final class Situs
{
    public static function nama(): string
    {
        return Settings::get('nama_situs', (string) config('app.name')) ?? (string) config('app.name');
    }

    public static function tagline(): string
    {
        return Settings::get('tagline', '') ?? '';
    }
}

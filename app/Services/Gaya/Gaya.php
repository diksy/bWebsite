<?php

namespace App\Services\Gaya;

use App\Models\GayaKustom;
use App\Support\Settings;

/** Gaya desain situs publik: tiga gaya bawaan (public/gaya/*.css) + gaya kustom terunggah. */
class Gaya
{
    public const DEFAULT = 'minimalis';

    public const BAWAAN = [
        'minimalis' => ['nama' => 'Minimalis', 'info' => 'Bersih, banyak ruang putih, aksen hijau. Default.'],
        'neo-brutalism' => ['nama' => 'Neo Brutalism', 'info' => 'Garis tebal hitam, bayangan keras, warna mencolok.'],
        'glassmorphism' => ['nama' => 'Glassmorphism', 'info' => 'Kartu kaca transparan di atas latar gradien.'],
    ];

    public static function bawaan(string $slug): bool
    {
        return isset(self::BAWAAN[$slug]);
    }

    public static function ada(string $slug): bool
    {
        return self::bawaan($slug) || GayaKustom::query()->where('slug', $slug)->exists();
    }

    /** Slug gaya aktif; kembali ke minimalis bila gaya kustom sudah dihapus. */
    public static function aktif(): string
    {
        $slug = (string) Settings::get('gaya_aktif', self::DEFAULT);

        return self::ada($slug) ? $slug : self::DEFAULT;
    }

    public static function setAktif(string $slug): void
    {
        Settings::set('gaya_aktif', $slug);
    }

    /** URL berkas CSS untuk slug (bawaan atau kustom); null bila tidak ada. */
    public static function urlCss(string $slug): ?string
    {
        if (self::bawaan($slug)) {
            return self::urlPublik("gaya/{$slug}.css");
        }

        return GayaKustom::query()->where('slug', $slug)->first()?->url();
    }

    public static function urlDasar(): string
    {
        return self::urlPublik('gaya/dasar.css');
    }

    /** @return list<string> stylesheet untuk layout publik: struktur dasar lalu gaya aktif. */
    public static function stylesheet(?string $slug = null): array
    {
        $slug ??= self::aktif();

        return [self::urlDasar(), self::urlCss($slug) ?? self::urlCss(self::DEFAULT)];
    }

    private static function urlPublik(string $path): string
    {
        $v = @filemtime(public_path($path)) ?: 0;

        return asset($path)."?v={$v}";
    }
}

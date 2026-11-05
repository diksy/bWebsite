<?php

namespace App\Services\Gambar;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class WebpPengonversi implements Pengonversi
{
    public const LEBAR_MAKS = 1600;

    public const PIKSEL_MAKS = 40_000_000;

    private const JENIS = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

    public function simpan(string $biner): string
    {
        if (! function_exists('imagewebp')) {
            throw new RuntimeException('Ekstensi GD dengan dukungan WebP tidak tersedia di server.');
        }

        $info = @getimagesizefromstring($biner);
        if ($info === false || ! in_array($info[2], self::JENIS, true)) {
            throw new RuntimeException('File bukan gambar yang didukung (JPG, PNG, GIF, WebP).');
        }
        if ($info[0] * $info[1] > self::PIKSEL_MAKS) {
            throw new RuntimeException('Dimensi gambar terlalu besar.');
        }

        $src = @imagecreatefromstring($biner);
        if ($src === false) {
            throw new RuntimeException('Gambar tidak dapat dibaca.');
        }

        // Skala turun bila terlalu lebar; hasil re-encode otomatis membuang EXIF/metadata.
        if (imagesx($src) > self::LEBAR_MAKS) {
            $skala = imagescale($src, self::LEBAR_MAKS);
            if ($skala !== false) {
                $src = $skala;
            }
        }
        imagepalettetotruecolor($src);
        imagealphablending($src, true);
        imagesavealpha($src, true);

        ob_start();
        $ok = imagewebp($src, null, 82);
        $webp = (string) ob_get_clean();
        if (! $ok || $webp === '') {
            throw new RuntimeException('Konversi ke WebP gagal.');
        }

        $path = 'gambar/'.now()->format('Y/m').'/'.Str::lower(Str::random(24)).'.webp';
        Storage::disk('public')->put($path, $webp);

        return $path;
    }
}

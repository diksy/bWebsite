<?php

namespace App\Prototype;

class Dummy
{
    public static function site(): array
    {
        return ['nama' => 'Storika', 'tagline' => 'Cerita, tips, dan panduan untuk Anda'];
    }

    public static function kategori(): array
    {
        return [
            ['slug' => 'teknologi', 'nama' => 'Teknologi'],
            ['slug' => 'bisnis', 'nama' => 'Bisnis'],
            ['slug' => 'gaya-hidup', 'nama' => 'Gaya Hidup'],
        ];
    }
}

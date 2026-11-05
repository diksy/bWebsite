<?php

namespace App\Prototype;

/** Data dummy fase prototype — hapus saat build (diganti model nyata). */
class Dummy
{
    public const GAYA = [
        'minimalis' => ['nama' => 'Minimalis', 'info' => 'Bersih, banyak ruang putih, aksen hijau. Default.'],
        'neo-brutalism' => ['nama' => 'Neo Brutalism', 'info' => 'Garis tebal hitam, bayangan keras, warna mencolok.'],
        'glassmorphism' => ['nama' => 'Glassmorphism', 'info' => 'Kartu kaca transparan di atas latar gradien.'],
    ];

    public const KUSTOM = [
        ['slug' => 'senja-hangat', 'nama' => 'Senja Hangat', 'diunggah' => '2026-11-04', 'ukuran' => '3 KB'],
    ];

    public static function artikel(): array
    {
        return [
            ['slug' => 'cara-memulai-blog', 'judul' => 'Cara Memulai Blog yang Dibaca Orang', 'kategori' => 'Panduan', 'penulis' => 'Sari Dewi', 'tanggal' => '3 Nov 2026', 'ringkas' => 'Langkah praktis memilih topik, menulis konsisten, dan membangun pembaca pertama.'],
            ['slug' => 'seo-untuk-pemula', 'judul' => 'SEO untuk Pemula: Mulai dari Mana?', 'kategori' => 'SEO', 'penulis' => 'Budi Santoso', 'tanggal' => '1 Nov 2026', 'ringkas' => 'Kata kunci, judul, dan tautan internal yang benar-benar berpengaruh.'],
            ['slug' => 'menulis-dengan-ai', 'judul' => 'Menulis dengan Bantuan AI Tanpa Kehilangan Suara Sendiri', 'kategori' => 'Teknologi', 'penulis' => 'Sari Dewi', 'tanggal' => '28 Okt 2026', 'ringkas' => 'AI sebagai penyusun draf, editor manusia tetap penentu akhir.'],
        ];
    }
}

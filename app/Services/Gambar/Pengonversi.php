<?php

namespace App\Services\Gambar;

interface Pengonversi
{
    /**
     * Ubah gambar (biner) menjadi WebP dan simpan di disk "public".
     *
     * @return string path relatif di disk public (mis. gambar/2026/11/abc.webp)
     */
    public function simpan(string $biner): string;
}

<?php

namespace App\Services\Seo;

use App\Models\Artikel;
use Illuminate\Support\Collection;

/**
 * Saran link internal: artikel terbit yang relevan (kategori sama, kata kunci/kata judul muncul di isi).
 */
class SaranLinkInternal
{
    /** @return Collection<int, Artikel> */
    public function untuk(?int $artikelId, string $judul, string $kataKunci, string $isi, ?int $kategoriId, int $maks = 5): Collection
    {
        $isiKecil = mb_strtolower($judul.' '.$isi.' '.$kataKunci);
        $kk = mb_strtolower(trim($kataKunci));

        return Artikel::query()
            ->diterbitkan()
            ->when($artikelId, fn ($q) => $q->where('id', '!=', $artikelId))
            ->select(['id', 'judul', 'slug', 'kategori_id', 'kata_kunci'])
            ->latest('terbit_pada')
            ->limit(200)
            ->get()
            ->reject(fn (Artikel $a) => str_contains($isi, '/'.$a->slug))
            ->map(function (Artikel $a) use ($isiKecil, $kk, $kategoriId): Artikel {
                $skor = 0;
                if ($kategoriId !== null && $a->kategori_id === $kategoriId) {
                    $skor += 3;
                }
                foreach (preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($a->judul), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $kata) {
                    if (mb_strlen($kata) > 4 && str_contains($isiKecil, $kata)) {
                        $skor += 2;
                    }
                }
                if ($kk !== '' && (str_contains(mb_strtolower($a->judul), $kk) || ($a->kata_kunci && str_contains($kk, mb_strtolower($a->kata_kunci))))) {
                    $skor += 4;
                }
                $a->setAttribute('skor_relevansi', $skor);

                return $a;
            })
            ->filter(fn (Artikel $a) => $a->getAttribute('skor_relevansi') > 0)
            ->sortByDesc('skor_relevansi')
            ->take($maks)
            ->values();
    }
}

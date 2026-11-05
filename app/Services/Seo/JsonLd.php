<?php

namespace App\Services\Seo;

use App\Models\Artikel;
use App\Models\User;
use App\Support\Situs;
use Illuminate\Support\Facades\Storage;

/**
 * Pembangun JSON-LD (schema.org) untuk halaman publik.
 */
class JsonLd
{
    /** @return array<int, array<string, mixed>> */
    public function artikel(Artikel $a): array
    {
        $url = $a->url();
        $gambar = $a->gambar ? Storage::disk('public')->url($a->gambar) : null;

        $skema = [
            $this->hapusKosong([
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => mb_substr($a->judul, 0, 110),
                'description' => $a->ringkasan,
                'image' => $gambar,
                'datePublished' => $a->terbit_pada?->toIso8601String(),
                'dateModified' => $a->updated_at?->toIso8601String(),
                'inLanguage' => 'id-ID',
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
                'author' => $this->orang($a->penulis),
                'publisher' => ['@type' => 'Organization', 'name' => Situs::nama(), 'url' => url('/')],
                'articleSection' => $a->kategori?->nama,
                'keywords' => $a->tag->pluck('nama')->implode(', ') ?: null,
            ]),
            $this->breadcrumb(array_values(array_filter([
                ['Beranda', url('/')],
                $a->kategori ? [$a->kategori->nama, url('kategori/'.$a->kategori->slug)] : null,
                [$a->judul, $url],
            ]))),
        ];

        $faq = $a->faqLengkap();
        if ($faq !== []) {
            $skema[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array_map(fn (array $f) => [
                    '@type' => 'Question',
                    'name' => $f['q'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
                ], $faq),
            ];
        }

        return $skema;
    }

    /** @return array<int, array<string, mixed>> */
    public function penulis(User $p): array
    {
        return [
            ['@context' => 'https://schema.org'] + $this->orang($p, true),
            $this->breadcrumb([['Beranda', url('/')], [$p->name, url('penulis/'.$p->slug)]]),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function beranda(): array
    {
        return [[
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => Situs::nama(),
            'url' => url('/'),
            'inLanguage' => 'id-ID',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => url('cari').'?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ]];
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $butir
     * @return array<string, mixed>
     */
    public function breadcrumb(array $butir): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn (array $b, int $i) => [
                '@type' => 'ListItem', 'position' => $i + 1, 'name' => $b[0], 'item' => $b[1],
            ], $butir, array_keys($butir)),
        ];
    }

    /** @return array<string, mixed> */
    private function orang(User $u, bool $lengkap = false): array
    {
        return $this->hapusKosong([
            '@type' => 'Person',
            'name' => $u->name,
            'url' => url('penulis/'.$u->slug),
            'description' => $lengkap ? $u->bio : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function hapusKosong(array $data): array
    {
        return array_filter($data, fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    /** Serialisasi aman untuk ditanam di <script> (tanpa risiko menutup tag). */
    public static function tag(array $skema): string
    {
        $json = json_encode($skema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR);

        return '<script type="application/ld+json">'.$json.'</script>';
    }
}

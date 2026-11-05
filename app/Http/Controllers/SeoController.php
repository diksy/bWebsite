<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\Kategori;
use App\Models\Tag;
use App\Models\User;
use App\Support\Settings;
use App\Support\Situs;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = [['loc' => url('/'), 'lastmod' => Artikel::query()->diterbitkan()->max('updated_at')]];

        foreach (Kategori::query()->whereHas('artikel', fn ($q) => $q->diterbitkan())->get() as $k) {
            $urls[] = ['loc' => url('kategori/'.$k->slug), 'lastmod' => null];
        }
        foreach (Tag::query()->whereHas('artikel', fn ($q) => $q->diterbitkan())->get() as $t) {
            $urls[] = ['loc' => url('tag/'.$t->slug), 'lastmod' => null];
        }
        foreach (User::query()->where('is_active', true)->whereHas('artikel', fn ($q) => $q->diterbitkan())->get() as $p) {
            $urls[] = ['loc' => url('penulis/'.$p->slug), 'lastmod' => null];
        }
        foreach (Artikel::query()->diterbitkan()->orderByDesc('terbit_pada')->limit(45000)->get(['id', 'slug', 'updated_at']) as $a) {
            $urls[] = ['loc' => $a->url(), 'lastmod' => $a->updated_at];
        }

        return $this->xml(view('seo.sitemap', ['urls' => $urls])->render(), 'application/xml');
    }

    public function rss(): Response
    {
        $artikel = Artikel::query()->diterbitkan()->with(['penulis:id,name', 'kategori:id,nama'])->latest('terbit_pada')->limit(20)->get();

        return $this->xml(view('seo.rss', [
            'artikel' => $artikel,
            'judul' => Situs::nama(),
            'deskripsi' => Situs::tagline() ?: Situs::nama(),
            'dibangun' => $artikel->isNotEmpty() ? $artikel->first()->terbit_pada : now(),
            'gambarUrl' => fn (?string $p) => $p ? Storage::disk('public')->url($p) : null,
        ])->render(), 'application/rss+xml');
    }

    public function robots(): Response
    {
        $admin = trim((string) Settings::get('admin_path', 'admin'), '/');
        $isi = implode("\n", [
            'User-agent: *',
            'Disallow: /'.$admin.'/',
            'Disallow: /sso/',
            'Disallow: /cari',
            'Allow: /',
            '',
            'Sitemap: '.url('sitemap.xml'),
            '',
        ]);

        return response($isi, 200, ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'public, max-age=3600']);
    }

    private function xml(string $isi, string $tipe): Response
    {
        return response($isi, 200, ['Content-Type' => $tipe.'; charset=UTF-8', 'Cache-Control' => 'public, max-age=600']);
    }
}

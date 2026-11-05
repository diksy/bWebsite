<?php

namespace App\Services\Seo;

use App\Support\Markdown;

/**
 * Skor SEO berbasis aturan (tanpa AI). Tiap aturan punya bobot; aturan kata kunci
 * dilewati bila kata kunci utama belum diisi.
 */
class SkorSeo
{
    /**
     * @param  array<int, array{q?: string, a?: string}>  $faq
     * @return array{skor: int, cek: array<int, array{ok: bool, teks: string, bobot: int}>, kata: int, perlu_kata_kunci: bool}
     */
    public function analisis(
        string $judul,
        string $ringkasan,
        string $kataKunci,
        string $isi,
        ?string $gambar,
        string $gambarAlt,
        array $faq = [],
        ?string $hostSitus = null,
    ): array {
        $teks = $this->teksPolos($isi);
        $kata = $teks === '' ? 0 : count(preg_split('/\s+/u', $teks) ?: []);
        $kk = mb_strtolower(trim($kataKunci));
        $cek = [];

        $tambah = function (bool $ok, string $teks, int $bobot) use (&$cek): void {
            $cek[] = ['ok' => $ok, 'teks' => $teks, 'bobot' => $bobot];
        };

        $pj = mb_strlen(trim($judul));
        $tambah($pj >= 30 && $pj <= 60, "Judul 30–60 karakter (sekarang {$pj})", 10);

        $pm = mb_strlen(trim($ringkasan));
        $tambah($pm >= 120 && $pm <= 160, "Meta description 120–160 karakter (sekarang {$pm})", 10);

        $tambah($kk !== '', 'Kata kunci utama diisi', 5);
        if ($kk !== '') {
            $tambah(str_contains(mb_strtolower($judul), $kk), 'Kata kunci ada di judul', 10);
            $seratus = implode(' ', array_slice(preg_split('/\s+/u', $teks) ?: [], 0, 100));
            $tambah(str_contains(mb_strtolower($seratus), $kk), 'Kata kunci muncul di 100 kata pertama', 10);
            $tambah(str_contains(mb_strtolower($ringkasan), $kk), 'Kata kunci ada di meta description', 5);
            $jumlah = $kata > 0 ? mb_substr_count(mb_strtolower($teks), $kk) : 0;
            $kepadatan = $kata > 0 ? $jumlah / $kata * 100 : 0;
            $tambah($kepadatan >= 0.3 && $kepadatan <= 3.0, 'Kepadatan kata kunci wajar 0,3–3% (sekarang '.number_format($kepadatan, 1, ',', '').'%)', 5);
        }

        $h2 = preg_match_all('/^##\s+\S/m', $isi);
        $tambah($h2 >= 2, "Minimal 2 subjudul H2 (sekarang {$h2})", 10);
        $tambah($kata >= 600, "Panjang artikel ≥ 600 kata (sekarang {$kata})", 15);

        $internal = $this->hitungLinkInternal($isi, $hostSitus);
        $tambah($internal >= 2, "Minimal 2 link internal (sekarang {$internal})", 10);

        $tambah(filled($gambar) && filled($gambarAlt), 'Gambar unggulan dengan teks alt', 10);
        $tambah(! preg_match('/!\[\]\(/', $isi), 'Semua gambar di isi punya teks alt', 5);

        $faqLengkap = count(array_filter($faq, fn ($f) => filled($f['q'] ?? null) && filled($f['a'] ?? null)));
        $tambah($faqLengkap >= 2, "Minimal 2 FAQ (sekarang {$faqLengkap})", 5);

        $total = array_sum(array_column($cek, 'bobot'));
        $dapat = array_sum(array_map(fn ($c) => $c['ok'] ? $c['bobot'] : 0, $cek));

        return [
            'skor' => $total > 0 ? (int) round($dapat / $total * 100) : 0,
            'cek' => $cek,
            'kata' => $kata,
            'perlu_kata_kunci' => $kk === '',
        ];
    }

    private function teksPolos(string $markdown): string
    {
        $teks = html_entity_decode(strip_tags(Markdown::render($markdown)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $teks) ?? '');
    }

    /** Link internal unik: URL relatif ("/..."), atau host sama dengan situs. */
    private function hitungLinkInternal(string $isi, ?string $host): int
    {
        $host ??= parse_url((string) config('app.url'), PHP_URL_HOST);
        preg_match_all('/(?<!!)\[[^\]]*\]\(([^)\s]+)/', $isi, $m);

        $unik = [];
        foreach ($m[1] as $url) {
            $h = parse_url($url, PHP_URL_HOST);
            $internal = (str_starts_with($url, '/') && ! str_starts_with($url, '//')) || ($h !== null && $h === $host);
            if ($internal) {
                $unik[rtrim((string) parse_url($url, PHP_URL_PATH), '/')] = true;
            }
        }

        return count($unik);
    }
}

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

    public static function tags(): array
    {
        return ['seo', 'umkm', 'produktivitas', 'ai', 'marketing'];
    }

    public static function penulis(): array
    {
        return [
            ['slug' => 'redaksi', 'nama' => 'Redaksi Storika', 'bio' => 'Tim redaksi yang merangkum topik terkini dengan sumber terpercaya.'],
            ['slug' => 'sari-wulandari', 'nama' => 'Sari Wulandari', 'bio' => 'Penulis bisnis dan UMKM, berpengalaman 8 tahun di media digital.'],
        ];
    }

    public static function artikel(): array
    {
        $isi = "Paragraf pembuka artikel ini menjelaskan inti topik dalam dua kalimat agar pembaca langsung paham manfaatnya.\n\n## Mengapa penting\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore.\n\n## Langkah praktis\n1. Tentukan tujuan.\n2. Kumpulkan data.\n3. Terapkan dan ukur.";
        $faq = [
            ['q' => 'Apakah cocok untuk pemula?', 'a' => 'Ya, langkahnya dibuat sederhana dan bertahap.'],
            ['q' => 'Berapa lama hasilnya terlihat?', 'a' => 'Umumnya 2–3 bulan tergantung konsistensi.'],
        ];
        $judul = [
            ['Panduan SEO untuk Blog Baru', 'teknologi', 'redaksi', ['seo', 'marketing']],
            ['5 Strategi Marketing UMKM Hemat Biaya', 'bisnis', 'sari-wulandari', ['umkm', 'marketing']],
            ['Cara Memakai AI untuk Riset Konten', 'teknologi', 'redaksi', ['ai', 'seo']],
            ['Rutinitas Pagi yang Produktif', 'gaya-hidup', 'sari-wulandari', ['produktivitas']],
            ['Membangun Merek Lokal dari Nol', 'bisnis', 'sari-wulandari', ['umkm']],
            ['Tren Teknologi 2027 yang Perlu Dipantau', 'teknologi', 'redaksi', ['ai']],
        ];
        $out = [];
        foreach ($judul as $i => [$j, $kat, $pen, $tags]) {
            $out[] = [
                'id' => $i + 1,
                'slug' => str($j)->slug()->toString(),
                'judul' => $j,
                'ringkasan' => 'Ringkasan singkat tentang '.strtolower($j).' dalam dua kalimat yang menarik pembaca.',
                'kategori' => $kat,
                'penulis' => $pen,
                'tags' => $tags,
                'tanggal' => '2026-10-'.str_pad((string) (28 - $i * 3), 2, '0', STR_PAD_LEFT),
                'gambar' => 'https://placehold.co/800x450/d1fae5/065f46?text='.urlencode('Artikel '.($i + 1)),
                'isi' => $isi,
                'faq' => $faq,
                'status' => ['terbit', 'terbit', 'draf', 'terbit', 'terjadwal', 'draf'][$i],
                'skor' => [82, 74, 58, 90, 69, 45][$i],
                'sumber_ai' => in_array($i, [2, 5], true),
            ];
        }

        return $out;
    }

    public static function temaTerjadwal(): array
    {
        return [
            ['id' => 1, 'tema' => 'Tips SEO untuk blog UMKM', 'kata_kunci' => 'seo umkm, blog bisnis', 'frekuensi' => 'Harian', 'kategori' => 'Teknologi', 'penulis' => 'Redaksi Storika', 'batas' => 3, 'aktif' => true],
            ['id' => 2, 'tema' => 'Strategi marketing digital', 'kata_kunci' => 'marketing, iklan hemat', 'frekuensi' => 'Mingguan', 'kategori' => 'Bisnis', 'penulis' => 'Sari Wulandari', 'batas' => 2, 'aktif' => false],
        ];
    }

    public static function logAi(): array
    {
        return [
            ['waktu' => '2026-11-05 06:00', 'job' => 'Cari web', 'tema' => 'Tips SEO untuk blog UMKM', 'token' => 0, 'status' => 'ok', 'pesan' => '5 sumber ditemukan (Tavily)'],
            ['waktu' => '2026-11-05 06:01', 'job' => 'Ringkas sumber', 'tema' => 'Tips SEO untuk blog UMKM', 'token' => 3120, 'status' => 'ok', 'pesan' => ''],
            ['waktu' => '2026-11-05 06:02', 'job' => 'Outline', 'tema' => 'Tips SEO untuk blog UMKM', 'token' => 940, 'status' => 'ok', 'pesan' => ''],
            ['waktu' => '2026-11-05 06:03', 'job' => 'Draf + meta + FAQ', 'tema' => 'Tips SEO untuk blog UMKM', 'token' => 5210, 'status' => 'ok', 'pesan' => 'Draf dibuat'],
            ['waktu' => '2026-11-04 06:01', 'job' => 'Ringkas sumber', 'tema' => 'Strategi marketing digital', 'token' => 0, 'status' => 'error', 'pesan' => '429 Too Many Requests dari API model'],
        ];
    }
}

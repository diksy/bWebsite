<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
    <title>{{ $judul }}</title>
    <link>{{ url('/') }}</link>
    <description>{{ $deskripsi }}</description>
    <language>id-ID</language>
    <lastBuildDate>{{ $dibangun->toRssString() }}</lastBuildDate>
    <atom:link href="{{ route('rss') }}" rel="self" type="application/rss+xml"/>
@foreach ($artikel as $a)
    <item>
        <title>{{ $a->judul }}</title>
        <link>{{ $a->url() }}</link>
        <guid isPermaLink="true">{{ $a->url() }}</guid>
        <pubDate>{{ $a->terbit_pada->toRssString() }}</pubDate>
        <dc:creator>{{ $a->penulis->name }}</dc:creator>
@if ($a->kategori)        <category>{{ $a->kategori->nama }}</category>
@endif
        <description>{{ $a->ringkasan }}</description>
        <content:encoded><![CDATA[{!! str_replace(']]>', ']]]]><![CDATA[>', (string) $a->isi_html) !!}]]></content:encoded>
@if ($img = $gambarUrl($a->gambar))        <enclosure url="{{ $img }}" type="image/webp" length="0"/>
@endif
    </item>
@endforeach
</channel>
</rss>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $meta['judul'] }}</title>
    <meta name="description" content="{{ $meta['deskripsi'] }}">
    <meta name="robots" content="{{ $meta['robots'] }}">
    <link rel="canonical" href="{{ $meta['kanonik'] }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="{{ \App\Support\Situs::nama() }}">
    <meta property="og:type" content="{{ $meta['tipe'] }}">
    <meta property="og:title" content="{{ $meta['judul'] }}">
    <meta property="og:description" content="{{ $meta['deskripsi'] }}">
    <meta property="og:url" content="{{ $meta['kanonik'] }}">
    @if ($meta['gambar'])<meta property="og:image" content="{{ $meta['gambar'] }}">@endif
    <meta name="twitter:card" content="{{ $meta['gambar'] ? 'summary_large_image' : 'summary' }}">
    @if (Route::has('rss'))<link rel="alternate" type="application/rss+xml" title="{{ \App\Support\Situs::nama() }}" href="{{ route('rss') }}">@endif
    @foreach (($jsonld ?? []) as $skema){!! \App\Services\Seo\JsonLd::tag($skema) !!}
    @endforeach
    @stack('head')
    @foreach (\App\Services\Gaya\Gaya::stylesheet() as $css)<link rel="stylesheet" href="{{ $css }}">
    @endforeach
</head>
<body class="g-body">
@php($menuKategori = \App\Models\Kategori::query()->orderBy('nama')->get(['nama', 'slug']))
<header class="g-header">
    <div class="g-wrap">
        <a href="{{ route('beranda') }}" class="g-brand">{{ \App\Support\Situs::nama() }}</a>
        <nav class="g-nav">
            @foreach ($menuKategori as $k)<a href="{{ route('kategori', $k->slug) }}">{{ $k->nama }}</a>@endforeach
        </nav>
        <form action="{{ route('cari') }}" class="g-cari-atas"><input type="search" name="q" placeholder="Cari artikel…" aria-label="Cari artikel" class="g-input"></form>
        <details class="g-menu-seluler">
            <summary>Menu</summary>
            <div>
                @foreach ($menuKategori as $k)<a href="{{ route('kategori', $k->slug) }}">{{ $k->nama }}</a>@endforeach
                <form action="{{ route('cari') }}"><input type="search" name="q" placeholder="Cari artikel…" aria-label="Cari artikel" class="g-input" style="width:100%"></form>
            </div>
        </details>
    </div>
</header>
<main class="g-main"><div class="g-wrap">@yield('content')</div></main>
<footer class="g-footer"><div class="g-wrap">
    © {{ date('Y') }} {{ \App\Support\Situs::nama() }}@if (Route::has('rss')) · <a href="{{ route('rss') }}">RSS</a>@endif
</div></footer>
</body>
</html>

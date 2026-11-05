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
    @stack('head')
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-slate-800 antialiased">
@php($menuKategori = \App\Models\Kategori::query()->orderBy('nama')->get(['nama', 'slug']))
<header class="border-b border-slate-200">
    <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4">
        <a href="{{ route('beranda') }}" class="text-xl font-bold text-emerald-700">{{ \App\Support\Situs::nama() }}</a>
        <nav class="hidden gap-6 text-sm font-medium md:flex">
            @foreach ($menuKategori as $k)<a href="{{ route('kategori', $k->slug) }}" class="hover:text-emerald-700">{{ $k->nama }}</a>@endforeach
        </nav>
        <form action="{{ route('cari') }}" class="hidden md:block"><input type="search" name="q" placeholder="Cari artikel…" aria-label="Cari artikel" class="rounded-full border border-slate-300 px-4 py-1.5 text-sm"></form>
    </div>
    <details class="border-t border-slate-100 md:hidden">
        <summary class="cursor-pointer px-4 py-2 text-sm font-medium">Menu</summary>
        <div class="flex flex-col gap-2 px-4 pb-3 text-sm">
            @foreach ($menuKategori as $k)<a href="{{ route('kategori', $k->slug) }}">{{ $k->nama }}</a>@endforeach
            <form action="{{ route('cari') }}"><input type="search" name="q" placeholder="Cari artikel…" aria-label="Cari artikel" class="w-full rounded-full border border-slate-300 px-4 py-1.5"></form>
        </div>
    </details>
</header>
<main class="mx-auto max-w-5xl px-4 py-8">@yield('content')</main>
<footer class="mt-12 border-t border-slate-200 py-6 text-center text-sm text-slate-500">
    © {{ date('Y') }} {{ \App\Support\Situs::nama() }}@if (Route::has('rss')) · <a href="{{ route('rss') }}" class="hover:underline">RSS</a>@endif
</footer>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $site['nama'])</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-slate-800 antialiased">
<header class="border-b border-slate-200">
    <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
        <a href="{{ route('proto.home') }}" class="text-xl font-bold text-emerald-700">{{ $site['nama'] }}</a>
        <nav class="hidden gap-6 text-sm font-medium md:flex">
            <a href="{{ route('proto.home') }}" class="hover:text-emerald-700">Beranda</a>
            @foreach ($kategori as $k)
                <a href="#" class="hover:text-emerald-700">{{ $k['nama'] }}</a>
            @endforeach
        </nav>
        <form action="#" class="hidden md:block">
            <input type="search" placeholder="Cari artikel…" class="rounded-full border border-slate-300 px-4 py-1.5 text-sm">
        </form>
    </div>
    <details class="border-t border-slate-100 md:hidden">
        <summary class="cursor-pointer px-4 py-2 text-sm font-medium">Menu</summary>
        <div class="flex flex-col gap-2 px-4 pb-3 text-sm">
            <a href="{{ route('proto.home') }}">Beranda</a>
            @foreach ($kategori as $k)
                <a href="#">{{ $k['nama'] }}</a>
            @endforeach
            <input type="search" placeholder="Cari artikel…" class="rounded-full border border-slate-300 px-4 py-1.5">
        </div>
    </details>
</header>
<main class="mx-auto max-w-5xl px-4 py-8">
    @yield('content')
</main>
<footer class="mt-12 border-t border-slate-200 py-6 text-center text-sm text-slate-500">
    © {{ date('Y') }} {{ $site['nama'] }} · <a href="#" class="hover:underline">RSS</a>
</footer>
</body>
</html>

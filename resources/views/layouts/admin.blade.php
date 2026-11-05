<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — Admin {{ $site['nama'] }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
@php($menu = [['proto.admin','Dashboard'],['proto.admin.artikel','Artikel'],['proto.admin.tema','Tema terjadwal'],['proto.admin.pengaturan','Pengaturan AI'],['proto.admin.log','Log AI'],['proto.admin.situs','Pengaturan situs']])
<div class="md:flex">
    <aside class="bg-slate-900 text-slate-200 md:min-h-screen md:w-56">
        <div class="flex items-center justify-between px-4 py-3 font-bold md:block">{{ $site['nama'] }} <span class="text-xs font-normal text-slate-400">Admin</span></div>
        <nav class="flex gap-1 overflow-x-auto px-2 pb-2 text-sm md:flex-col md:pb-0">
            @foreach ($menu as [$r, $l])
                <a href="{{ route($r) }}" class="whitespace-nowrap rounded px-3 py-2 hover:bg-slate-800 {{ request()->routeIs($r) ? 'bg-slate-800 font-semibold text-white' : '' }}">{{ $l }}</a>
            @endforeach
            <a href="{{ route('proto.login') }}" class="whitespace-nowrap rounded px-3 py-2 text-slate-400 hover:bg-slate-800">Keluar</a>
        </nav>
    </aside>
    <main class="flex-1 p-4 md:p-8">@yield('content')</main>
</div>
</body>
</html>

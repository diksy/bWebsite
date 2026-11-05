<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
@php
    $menu = collect([
        ['admin.dasbor', 'Dashboard', false],
        ['admin.artikel.index', 'Artikel', false],
        ['admin.kategori.index', 'Kategori & tag', false],
        ['admin.tema.index', 'Tema terjadwal', true],
        ['admin.pengaturan.ai', 'Pengaturan AI', true],
        ['admin.log-ai', 'Log AI', true],
        ['admin.pengguna.index', 'Pengguna', true],
        ['admin.tampilan', 'Tampilan', true],
        ['admin.pengaturan.situs', 'Pengaturan situs', true],
    ])->filter(fn ($m) => Route::has($m[0]) && (! $m[2] || auth()->user()?->isAdmin()));
@endphp
<div class="md:flex">
    <aside class="bg-slate-900 text-slate-200 md:min-h-screen md:w-56">
        <div class="px-4 py-3 font-bold">{{ config('app.name') }} <span class="text-xs font-normal text-slate-400">{{ auth()->user()?->peran }}</span></div>
        <nav class="flex gap-1 overflow-x-auto px-2 pb-2 text-sm md:flex-col md:pb-0">
            @foreach ($menu as [$r, $l])
                <a href="{{ route($r) }}" class="whitespace-nowrap rounded px-3 py-2 hover:bg-slate-800 {{ request()->routeIs($r) ? 'bg-slate-800 font-semibold text-white' : '' }}">{{ $l }}</a>
            @endforeach
            <form method="post" action="{{ route('admin.keluar') }}">@csrf<button class="w-full whitespace-nowrap rounded px-3 py-2 text-left text-slate-400 hover:bg-slate-800">Keluar</button></form>
        </nav>
    </aside>
    <main class="flex-1 p-4 md:p-8">
        @if (session('status'))<div class="mb-4 rounded bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="mb-4 rounded bg-red-50 p-3 text-sm text-red-700">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
        @yield('content')
    </main>
</div>
@livewireScripts
</body>
</html>

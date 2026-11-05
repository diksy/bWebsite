<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Masuk — {{ $site['nama'] }}</title>@vite(['resources/css/app.css'])</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 p-4">
<form action="{{ route('proto.admin') }}" class="w-full max-w-sm space-y-4 rounded-xl bg-white p-6 shadow">
    <h1 class="text-xl font-bold">Masuk ke {{ $site['nama'] }}</h1>
    <label class="block text-sm font-medium">Email<input type="email" value="admin@storika.id" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
    <label class="block text-sm font-medium">Kata sandi<input type="password" value="rahasia" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
    <button class="w-full rounded bg-emerald-700 py-2 font-medium text-white">Masuk</button>
    <p class="text-center text-xs text-slate-400">Prototype: tombol langsung ke dashboard. Peran: admin &amp; penulis.</p>
</form></body></html>

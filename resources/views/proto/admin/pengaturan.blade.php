@extends('layouts.admin')
@section('title', 'Pengaturan AI')
@section('content')
<h1 class="text-2xl font-bold">Pengaturan AI</h1>
<form class="mt-4 max-w-2xl space-y-6">
    <section class="space-y-3 rounded-xl bg-white p-5 shadow-sm"><h2 class="font-bold">Model (kompatibel OpenAI)</h2>
        <label class="block text-sm">Base URL<input value="https://api.openai.com/v1" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">API key <span class="text-slate-400">(tersimpan terenkripsi)</span><input type="password" value="sk-••••••••••••" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">Nama model<input value="gpt-4o-mini" class="mt-1 w-full rounded border px-3 py-2"></label>
        <div class="flex items-center gap-3"><button type="button" class="rounded border border-emerald-700 px-4 py-2 text-sm text-emerald-700">Uji koneksi</button><span class="text-sm text-emerald-700">✓ Terhubung (contoh hasil)</span></div></section>
    <section class="space-y-3 rounded-xl bg-white p-5 shadow-sm"><h2 class="font-bold">Pencarian web</h2>
        <label class="block text-sm">Penyedia<select class="mt-1 w-full rounded border px-3 py-2"><option>Tavily (default)</option><option>Adapter lain…</option></select></label>
        <label class="block text-sm">API key<input type="password" value="tvly-••••••••" class="mt-1 w-full rounded border px-3 py-2"></label></section>
    <button type="button" class="rounded bg-emerald-700 px-5 py-2 font-medium text-white">Simpan pengaturan</button>
</form>
@endsection

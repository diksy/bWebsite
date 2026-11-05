@extends('layouts.panel')
@section('title', 'Pengaturan AI')
@section('content')
<h1 class="text-2xl font-bold">Pengaturan AI</h1>
<form method="post" action="{{ route('admin.pengaturan.ai.simpan') }}" class="mt-4 max-w-2xl space-y-6">@csrf @method('PUT')
    <section class="space-y-3 rounded-xl bg-white p-5 shadow-sm"><h2 class="font-bold">Model (kompatibel OpenAI)</h2>
        <label class="block text-sm">Base URL<input name="ai_base_url" value="{{ old('ai_base_url', $baseUrl) }}" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">API key <span class="text-slate-400">(terenkripsi{{ $adaAiKey ? '; tersimpan, kosongkan bila tidak diubah' : '' }})</span><input name="ai_api_key" type="password" autocomplete="new-password" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">Nama model<input name="ai_model" value="{{ old('ai_model', $model) }}" class="mt-1 w-full rounded border px-3 py-2"></label></section>
    <section class="space-y-3 rounded-xl bg-white p-5 shadow-sm"><h2 class="font-bold">Pencarian web</h2>
        <label class="block text-sm">Penyedia<select name="search_provider" class="mt-1 w-full rounded border px-3 py-2">@foreach ($adapter as $a)<option value="{{ $a }}" @selected(old('search_provider', $provider) === $a)>{{ ucfirst($a) }}{{ $a === 'tavily' ? ' (default)' : '' }}</option>@endforeach</select></label>
        <label class="block text-sm">API key <span class="text-slate-400">(terenkripsi{{ $adaSearchKey ? '; tersimpan, kosongkan bila tidak diubah' : '' }})</span><input name="search_api_key" type="password" autocomplete="new-password" class="mt-1 w-full rounded border px-3 py-2"></label></section>
    <button class="rounded bg-emerald-700 px-5 py-2 font-medium text-white">Simpan pengaturan</button>
</form>
<div class="mt-3 flex gap-3">
    <form method="post" action="{{ route('admin.pengaturan.ai.uji-ai') }}">@csrf<button class="rounded border border-emerald-700 px-4 py-2 text-sm text-emerald-700">Uji koneksi model</button></form>
    <form method="post" action="{{ route('admin.pengaturan.ai.uji-pencarian') }}">@csrf<button class="rounded border border-emerald-700 px-4 py-2 text-sm text-emerald-700">Uji pencarian web</button></form>
</div>
@endsection

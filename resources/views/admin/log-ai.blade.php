@extends('layouts.panel')
@section('title', 'Log AI')
@section('content')
<h1 class="text-2xl font-bold">Log AI</h1>
<p class="mt-1 text-sm text-slate-500">Token 7 hari: {{ number_format($ringkas['token7'], 0, ',', '.') }} · 30 hari: {{ number_format($ringkas['token30'], 0, ',', '.') }} · Error 7 hari: {{ $ringkas['error7'] }}</p>
@if ($runGagal->isNotEmpty())
<section class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4"><h2 class="font-bold text-red-800">Proses gagal terbaru</h2>
    <ul class="mt-2 space-y-1 text-sm text-red-900">@foreach ($runGagal as $r)<li>{{ $r->updated_at->timezone(config('app.timezone'))->format('d M H:i') }} · {{ $r->tema?->nama ?? 'tema dihapus' }} — {{ $r->pesan }}</li>@endforeach</ul></section>
@endif
<div class="mt-4 flex gap-2 text-sm">
    <a href="{{ route('admin.log-ai') }}" class="rounded-full px-3 py-1 {{ ! $status ? 'bg-slate-800 text-white' : 'bg-white shadow-sm' }}">Semua</a>
    <a href="{{ route('admin.log-ai', ['status' => 'error']) }}" class="rounded-full px-3 py-1 {{ $status ? 'bg-slate-800 text-white' : 'bg-white shadow-sm' }}">Error saja</a>
</div>
<div class="mt-4 overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-left text-sm">
    <thead class="border-b text-slate-500"><tr><th class="p-3">Waktu</th><th>Tahap</th><th>Tema</th><th class="text-right">Token masuk</th><th class="text-right">Token keluar</th><th class="text-right">Durasi</th><th>Status</th><th>Pesan</th></tr></thead>
    <tbody class="divide-y">@forelse ($log as $l)
        <tr class="{{ $l->status === 'error' ? 'bg-red-50' : '' }}"><td class="p-3 whitespace-nowrap">{{ $l->created_at->timezone(config('app.timezone'))->format('d M H:i:s') }}</td><td>{{ str_replace('_', ' ', $l->tahap) }}</td><td>{{ $l->run?->tema?->nama ?? '—' }}</td>
        <td class="text-right">{{ number_format($l->token_masuk, 0, ',', '.') }}</td><td class="text-right">{{ number_format($l->token_keluar, 0, ',', '.') }}</td><td class="text-right">{{ number_format($l->durasi_ms / 1000, 1, ',', '') }} dtk</td>
        <td>{{ $l->status === 'ok' ? '✓' : '✗ Error' }}</td><td class="max-w-xs truncate" title="{{ $l->pesan }}">{{ $l->pesan }}</td></tr>
    @empty<tr><td colspan="8" class="p-6 text-center text-slate-500">Belum ada log.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $log->links() }}</div>
@endsection

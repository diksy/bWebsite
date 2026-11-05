@extends('layouts.admin')
@section('title', 'Log AI')
@section('content')
<h1 class="text-2xl font-bold">Log AI</h1>
<p class="mt-1 text-sm text-slate-500">Total token: {{ number_format(collect($log)->sum('token')) }} · Error: {{ collect($log)->where('status','error')->count() }}</p>
<div class="mt-4 overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-left text-sm">
    <thead class="border-b text-slate-500"><tr><th class="p-3">Waktu</th><th>Tahap</th><th>Tema</th><th>Token</th><th>Status</th><th>Pesan</th></tr></thead>
    <tbody class="divide-y">@foreach ($log as $l)<tr class="{{ $l['status']==='error' ? 'bg-red-50' : '' }}"><td class="p-3">{{ $l['waktu'] }}</td><td>{{ $l['job'] }}</td><td>{{ $l['tema'] }}</td><td>{{ number_format($l['token']) }}</td><td>{{ $l['status'] === 'ok' ? '✓' : '✗ Error' }}</td><td>{{ $l['pesan'] }}</td></tr>@endforeach</tbody></table></div>
@endsection

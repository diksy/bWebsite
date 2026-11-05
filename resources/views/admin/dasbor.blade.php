@extends('layouts.panel')
@section('title', 'Dashboard')
@section('content')
    <h1 class="text-2xl font-bold">Halo, {{ auth()->user()->name }}</h1>
    <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-6">
        @foreach ($hitung as $l => $n)<a href="{{ route('admin.artikel.index', ['status' => strtolower($l)]) }}" class="rounded-xl bg-white p-4 shadow-sm"><p class="text-sm text-slate-500">{{ $l }}</p><p class="text-3xl font-bold">{{ $n }}</p></a>@endforeach
        @if ($ai)
            <a href="{{ route('admin.log-ai') }}" class="rounded-xl bg-white p-4 shadow-sm"><p class="text-sm text-slate-500">Token AI (7 hari)</p><p class="text-3xl font-bold">{{ number_format($ai['token'], 0, ',', '.') }}</p></a>
            <a href="{{ route('admin.log-ai', ['status' => 'error']) }}" class="rounded-xl bg-white p-4 shadow-sm"><p class="text-sm text-slate-500">Error AI (7 hari)</p><p class="text-3xl font-bold {{ $ai['error'] ? 'text-red-600' : '' }}">{{ $ai['error'] }}</p></a>
            <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-sm text-slate-500">Draf AI menunggu tinjauan</p><p class="text-3xl font-bold text-violet-700">{{ $ai['drafAi'] }}</p></div>
        @endif
    </div>
    <h2 class="mt-8 text-lg font-bold">Draf terbaru</h2>
    <ul class="mt-2 divide-y rounded-xl bg-white shadow-sm">
        @forelse ($draf as $a)<li class="flex items-center justify-between p-3"><span>{{ $a->judul }} @if($a->dibuat_ai)<span class="ml-1 rounded bg-violet-100 px-1.5 text-xs text-violet-700">AI</span>@endif <span class="text-xs text-slate-400">· {{ $a->penulis->name }}</span></span><a class="text-sm text-emerald-700" href="{{ route('admin.artikel.edit', $a) }}">Tinjau</a></li>
        @empty<li class="p-4 text-sm text-slate-500">Tidak ada draf.</li>@endforelse
    </ul>
@endsection

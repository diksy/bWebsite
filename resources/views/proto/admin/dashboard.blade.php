@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
    <h1 class="text-2xl font-bold">Dashboard</h1>
    <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-5">
        @foreach ($hitung as $l => $n)<div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-sm text-slate-500">{{ $l }}</p><p class="text-3xl font-bold">{{ $n }}</p></div>@endforeach
        <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-sm text-slate-500">Token minggu ini</p><p class="text-3xl font-bold">{{ number_format($tokenMinggu) }}</p></div>
        <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-sm text-slate-500">Error AI</p><p class="text-3xl font-bold text-red-600">{{ $errorMinggu }}</p></div>
    </div>
    <h2 class="mt-8 text-lg font-bold">Draf menunggu tinjauan</h2>
    <ul class="mt-2 divide-y rounded-xl bg-white shadow-sm">
        @foreach ($draf as $a)<li class="flex items-center justify-between p-3"><span>{{ $a['judul'] }} @if($a['sumber_ai'])<span class="ml-1 rounded bg-violet-100 px-1.5 text-xs text-violet-700">AI</span>@endif</span><a class="text-sm text-emerald-700" href="{{ route('proto.admin.editor', $a['id']) }}">Tinjau</a></li>@endforeach
    </ul>
@endsection

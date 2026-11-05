@extends('layouts.publik')
@section('content')
    <div class="flex items-center gap-4"><div class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-200 text-2xl font-bold text-emerald-800" aria-hidden="true">{{ mb_substr($p->name, 0, 1) }}</div><div><h1 class="text-3xl font-bold">{{ $p->name }}</h1>@if ($p->bio)<p class="text-slate-600">{{ $p->bio }}</p>@endif</div></div>
    <h2 class="mt-10 text-xl font-bold">Artikel oleh {{ $p->name }}</h2>
    <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($daftar as $a) @include('publik.partials.kartu') @endforeach</div>
    <div class="mt-8">{{ $daftar->links() }}</div>
@endsection

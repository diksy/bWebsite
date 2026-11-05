@extends('layouts.publik')
@section('title', 'Cari — '.$site['nama'])
@section('content')
    <h1 class="text-3xl font-bold">Pencarian</h1>
    <form class="mt-4 flex gap-2"><input type="search" name="q" value="{{ $q }}" placeholder="Ketik kata kunci…" class="w-full rounded-lg border border-slate-300 px-4 py-2"><button class="rounded-lg bg-emerald-700 px-5 py-2 font-medium text-white">Cari</button></form>
    @if ($q !== '')<p class="mt-4 text-sm text-slate-500">{{ $hasil->count() }} hasil untuk “{{ $q }}”</p>@endif
    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($hasil as $a) @include('proto.partials.kartu', ['a' => $a]) @endforeach</div>
@endsection

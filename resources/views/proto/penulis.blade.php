@extends('layouts.publik')
@section('title', $p['nama'].' — '.$site['nama'])
@section('content')
    <div class="flex items-center gap-4"><div class="h-20 w-20 rounded-full bg-emerald-200"></div><div><h1 class="text-3xl font-bold">{{ $p['nama'] }}</h1><p class="text-slate-600">{{ $p['bio'] }}</p></div></div>
    <h2 class="mt-10 text-xl font-bold">Artikel oleh {{ $p['nama'] }}</h2>
    <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($artikel as $a) @include('proto.partials.kartu', ['a' => $a]) @endforeach</div>
@endsection

@extends('layouts.publik')
@section('title', $judul.' — '.$site['nama'])
@section('content')
    <nav class="text-sm text-slate-500"><a href="{{ route('proto.home') }}">Beranda</a> / {{ $judul }}</nav>
    <h1 class="mt-2 text-3xl font-bold">{{ $judul }}</h1>
    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($artikel as $a) @include('proto.partials.kartu', ['a' => $a]) @empty <p class="text-slate-500">Belum ada artikel.</p> @endforelse
    </div>
@endsection

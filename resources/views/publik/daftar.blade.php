@extends('layouts.publik')
@section('content')
    <nav aria-label="Breadcrumb" class="text-sm text-slate-500"><a href="{{ route('beranda') }}">Beranda</a> / {{ $jenis }} / {{ $judul }}</nav>
    <h1 class="mt-2 text-3xl font-bold">{{ $judul }}</h1>
    @if ($deskripsi)<p class="mt-1 text-slate-600">{{ $deskripsi }}</p>@endif
    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@forelse ($daftar as $a) @include('publik.partials.kartu') @empty <p class="text-slate-500">Belum ada artikel.</p> @endforelse</div>
    <div class="mt-8">{{ $daftar->links() }}</div>
@endsection

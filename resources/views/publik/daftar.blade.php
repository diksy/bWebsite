@extends('layouts.publik')
@section('content')
    <nav aria-label="Breadcrumb" class="g-crumb"><a href="{{ route('beranda') }}">Beranda</a> / {{ $jenis }} / {{ $judul }}</nav>
    <h1 class="g-title">{{ $judul }}</h1>
    @if ($deskripsi)<p class="g-meta">{{ $deskripsi }}</p>@endif
    <div class="g-grid" style="margin-top:1.5rem">@forelse ($daftar as $a) @include('publik.partials.kartu') @empty <p class="g-meta">Belum ada artikel.</p> @endforelse</div>
    {{ $daftar->links('publik.partials.paginasi') }}
@endsection

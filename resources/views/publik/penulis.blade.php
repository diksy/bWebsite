@extends('layouts.publik')
@section('content')
    <div class="g-author"><div class="g-avatar besar" aria-hidden="true">{{ mb_substr($p->name, 0, 1) }}</div><div><h1 class="g-title" style="margin:0">{{ $p->name }}</h1>@if ($p->bio)<p class="g-meta">{{ $p->bio }}</p>@endif</div></div>
    <h2 class="g-h2">Artikel oleh {{ $p->name }}</h2>
    <div class="g-grid">@foreach ($daftar as $a) @include('publik.partials.kartu') @endforeach</div>
    {{ $daftar->links('publik.partials.paginasi') }}
@endsection

@extends('layouts.publik')
@section('content')
    <h1 class="g-title">Pencarian</h1>
    <form action="{{ route('cari') }}" class="g-form"><input type="search" name="q" value="{{ $q }}" placeholder="Ketik kata kunci…" aria-label="Kata kunci" class="g-input"><button class="g-btn">Cari</button></form>
    @if ($hasil !== null)
        <p class="g-meta">{{ $hasil->total() }} hasil untuk “{{ $q }}”</p>
        <div class="g-grid" style="margin-top:1.5rem">@foreach ($hasil as $a) @include('publik.partials.kartu') @endforeach</div>
        {{ $hasil->links('publik.partials.paginasi') }}
    @elseif ($q !== '')<p class="g-meta">Ketik minimal 2 karakter.</p>@endif
@endsection

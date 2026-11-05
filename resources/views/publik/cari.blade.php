@extends('layouts.publik')
@section('content')
    <h1 class="text-3xl font-bold">Pencarian</h1>
    <form action="{{ route('cari') }}" class="mt-4 flex gap-2"><input type="search" name="q" value="{{ $q }}" placeholder="Ketik kata kunci…" aria-label="Kata kunci" class="w-full rounded-lg border border-slate-300 px-4 py-2"><button class="rounded-lg bg-emerald-700 px-5 py-2 font-medium text-white">Cari</button></form>
    @if ($hasil !== null)
        <p class="mt-4 text-sm text-slate-500">{{ $hasil->total() }} hasil untuk “{{ $q }}”</p>
        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($hasil as $a) @include('publik.partials.kartu') @endforeach</div>
        <div class="mt-8">{{ $hasil->links() }}</div>
    @elseif ($q !== '')<p class="mt-4 text-sm text-slate-500">Ketik minimal 2 karakter.</p>@endif
@endsection

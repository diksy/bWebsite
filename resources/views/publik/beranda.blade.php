@extends('layouts.publik')
@section('content')
    @if ($utama)
    <section class="grid gap-6 md:grid-cols-2">
        @if ($utama->gambar)<a href="{{ $utama->url() }}"><img src="{{ Storage::disk('public')->url($utama->gambar) }}" alt="{{ $utama->gambar_alt ?: $utama->judul }}" width="800" height="450" fetchpriority="high" class="aspect-video w-full rounded-xl object-cover"></a>@endif
        <div class="flex flex-col justify-center">
            <span class="text-xs font-semibold uppercase text-emerald-700">Terbaru</span>
            <h1 class="mt-2 text-3xl font-bold"><a href="{{ $utama->url() }}">{{ $utama->judul }}</a></h1>
            <p class="mt-3 text-slate-600">{{ $utama->ringkasan }}</p>
            <a href="{{ $utama->url() }}" class="mt-4 inline-block text-sm font-semibold text-emerald-700">Baca selengkapnya →</a>
        </div>
    </section>
    @else
        <h1 class="text-3xl font-bold">{{ \App\Support\Situs::nama() }}</h1>
        <p class="mt-2 text-slate-500">Belum ada artikel yang diterbitkan.</p>
    @endif
    @if ($artikel->isNotEmpty())
    <h2 class="mt-12 text-xl font-bold">Artikel</h2>
    <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($artikel as $a) @include('publik.partials.kartu') @endforeach</div>
    @endif
    <div class="mt-8">{{ $daftar->links() }}</div>
    @if ($tagPopuler->isNotEmpty())
    <h2 class="mt-12 text-xl font-bold">Tag populer</h2>
    <div class="mt-3 flex flex-wrap gap-2">@foreach ($tagPopuler as $t)<a href="{{ route('tag', $t->slug) }}" class="rounded-full bg-emerald-50 px-3 py-1 text-sm text-emerald-800">#{{ $t->nama }}</a>@endforeach</div>
    @endif
@endsection

@extends('layouts.publik')
@section('content')
    @if ($utama)
    <section class="g-hero">
        @if ($utama->gambar)<a href="{{ $utama->url() }}"><img src="{{ Storage::disk('public')->url($utama->gambar) }}" alt="{{ $utama->gambar_alt ?: $utama->judul }}" width="800" height="450" fetchpriority="high" class="g-img"></a>@endif
        <div>
            <span class="g-badge">Terbaru</span>
            <h1 class="g-title"><a href="{{ $utama->url() }}" style="text-decoration:none">{{ $utama->judul }}</a></h1>
            <p>{{ $utama->ringkasan }}</p>
            <a href="{{ $utama->url() }}" class="g-btn">Baca selengkapnya →</a>
        </div>
    </section>
    @else
        <h1 class="g-title">{{ \App\Support\Situs::nama() }}</h1>
        <p class="g-meta">Belum ada artikel yang diterbitkan.</p>
    @endif
    @if ($artikel->isNotEmpty())
    <h2 class="g-h2">Artikel</h2>
    <div class="g-grid">@foreach ($artikel as $a) @include('publik.partials.kartu') @endforeach</div>
    @endif
    {{ $daftar->links('publik.partials.paginasi') }}
    @if ($tagPopuler->isNotEmpty())
    <h2 class="g-h2">Tag populer</h2>
    <div class="g-tags">@foreach ($tagPopuler as $t)<a href="{{ route('tag', $t->slug) }}" class="g-pill">#{{ $t->nama }}</a>@endforeach</div>
    @endif
@endsection

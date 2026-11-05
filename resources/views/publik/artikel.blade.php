@extends('layouts.publik')
@section('content')
<article class="g-isi" style="margin:0 auto">
    <nav aria-label="Breadcrumb" class="g-crumb"><a href="{{ route('beranda') }}">Beranda</a>@if ($a->kategori) / <a href="{{ route('kategori', $a->kategori->slug) }}">{{ $a->kategori->nama }}</a>@endif / {{ $a->judul }}</nav>
    <h1 class="g-title">{{ $a->judul }}</h1>
    <p class="g-meta">Oleh <a class="g-link" href="{{ route('penulis', $a->penulis->slug) }}">{{ $a->penulis->name }}</a> · <time datetime="{{ $a->terbit_pada->toIso8601String() }}">{{ $a->terbit_pada->locale('id')->translatedFormat('d F Y') }}</time></p>
    @if ($a->gambar)<img src="{{ Storage::disk('public')->url($a->gambar) }}" alt="{{ $a->gambar_alt ?: $a->judul }}" width="1200" height="675" fetchpriority="high" class="g-img">@endif
    <div class="isi-artikel">{!! $a->isi_html !!}</div>
    @if ($a->tag->isNotEmpty())<div class="g-tags">@foreach ($a->tag as $t)<a href="{{ route('tag', $t->slug) }}" class="g-pill">#{{ $t->nama }}</a>@endforeach</div>@endif
    @if ($a->faqLengkap())
    <section class="g-faq"><h2 class="g-h2">Pertanyaan umum</h2>
        @foreach ($a->faqLengkap() as $f)<details><summary>{{ $f['q'] }}</summary><p>{{ $f['a'] }}</p></details>@endforeach</section>
    @endif
    @if ($a->sumber)
    <section class="g-sumber"><h2 class="g-h2" style="font-size:1rem">Sumber</h2><ul>@foreach ($a->sumber as $s)<li><a href="{{ $s['url'] }}" rel="nofollow noopener" target="_blank">{{ $s['judul'] ?? $s['url'] }}</a></li>@endforeach</ul></section>
    @endif
    <section class="g-card g-author" style="margin-top:2.5rem"><div class="g-avatar" aria-hidden="true">{{ mb_substr($a->penulis->name, 0, 1) }}</div>
        <div><a href="{{ route('penulis', $a->penulis->slug) }}" class="g-link">{{ $a->penulis->name }}</a>@if ($a->penulis->bio)<p class="g-meta">{{ $a->penulis->bio }}</p>@endif</div></section>
</article>
@if ($terkait->isNotEmpty())
<h2 class="g-h2">Artikel terkait</h2>
<div class="g-grid">@foreach ($terkait as $a) @include('publik.partials.kartu') @endforeach</div>
@endif
@endsection

@extends('layouts.publik')
@section('content')
<article class="mx-auto max-w-3xl">
    <nav aria-label="Breadcrumb" class="text-sm text-slate-500"><a href="{{ route('beranda') }}">Beranda</a>@if ($a->kategori) / <a href="{{ route('kategori', $a->kategori->slug) }}">{{ $a->kategori->nama }}</a>@endif / {{ $a->judul }}</nav>
    <h1 class="mt-3 text-3xl font-bold leading-tight">{{ $a->judul }}</h1>
    <p class="mt-2 text-sm text-slate-500">Oleh <a class="font-medium text-emerald-700" href="{{ route('penulis', $a->penulis->slug) }}">{{ $a->penulis->name }}</a> · <time datetime="{{ $a->terbit_pada->toIso8601String() }}">{{ $a->terbit_pada->locale('id')->translatedFormat('d F Y') }}</time></p>
    @if ($a->gambar)<img src="{{ Storage::disk('public')->url($a->gambar) }}" alt="{{ $a->gambar_alt ?: $a->judul }}" width="1200" height="675" fetchpriority="high" class="mt-6 aspect-video w-full rounded-xl object-cover">@endif
    <div class="isi-artikel mt-6 space-y-4 leading-relaxed [&_a]:text-emerald-700 [&_a]:underline [&_h2]:mt-6 [&_h2]:text-2xl [&_h2]:font-bold [&_h3]:mt-4 [&_h3]:text-xl [&_h3]:font-semibold [&_img]:rounded-lg [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6 [&_blockquote]:border-l-4 [&_blockquote]:pl-4 [&_blockquote]:text-slate-600">{!! $a->isi_html !!}</div>
    @if ($a->tag->isNotEmpty())<div class="mt-6 flex flex-wrap gap-2">@foreach ($a->tag as $t)<a href="{{ route('tag', $t->slug) }}" class="rounded-full bg-emerald-50 px-3 py-1 text-sm text-emerald-800">#{{ $t->nama }}</a>@endforeach</div>@endif
    @if ($a->faqLengkap())
    <section class="mt-10"><h2 class="text-xl font-bold">Pertanyaan umum</h2>
        @foreach ($a->faqLengkap() as $f)<details class="mt-2 rounded-lg border border-slate-200 p-3"><summary class="cursor-pointer font-medium">{{ $f['q'] }}</summary><p class="mt-2 text-slate-600">{{ $f['a'] }}</p></details>@endforeach</section>
    @endif
    @if ($a->sumber)
    <section class="mt-10 text-sm text-slate-600"><h2 class="text-base font-bold text-slate-800">Sumber</h2><ul class="mt-1 list-disc pl-5">@foreach ($a->sumber as $s)<li><a class="underline" href="{{ $s['url'] }}" rel="nofollow noopener" target="_blank">{{ $s['judul'] ?? $s['url'] }}</a></li>@endforeach</ul></section>
    @endif
    <section class="mt-10 flex gap-4 rounded-xl bg-slate-50 p-4"><div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-emerald-200 font-bold text-emerald-800" aria-hidden="true">{{ mb_substr($a->penulis->name, 0, 1) }}</div>
        <div><a href="{{ route('penulis', $a->penulis->slug) }}" class="font-bold">{{ $a->penulis->name }}</a>@if ($a->penulis->bio)<p class="text-sm text-slate-600">{{ $a->penulis->bio }}</p>@endif</div></section>
</article>
@if ($terkait->isNotEmpty())
<h2 class="mt-10 text-xl font-bold">Artikel terkait</h2>
<div class="mt-4 grid gap-6 sm:grid-cols-3">@foreach ($terkait as $a) @include('publik.partials.kartu') @endforeach</div>
@endif
@endsection

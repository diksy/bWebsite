@extends('layouts.publik')
@section('title', $a['judul'].' — '.$site['nama'])
@section('content')
<div class="mx-auto max-w-3xl">
    <nav class="text-sm text-slate-500"><a href="{{ route('proto.home') }}">Beranda</a> / <a href="{{ route('proto.kategori', $a['kategori']) }}">{{ ucfirst($a['kategori']) }}</a> / {{ $a['judul'] }}</nav>
    <h1 class="mt-3 text-3xl font-bold leading-tight">{{ $a['judul'] }}</h1>
    <p class="mt-2 text-sm text-slate-500">Oleh <a class="font-medium text-emerald-700" href="{{ route('proto.penulis', $penulisData['slug']) }}">{{ $penulisData['nama'] }}</a> · {{ $a['tanggal'] }}</p>
    <img src="{{ $a['gambar'] }}" alt="{{ $a['judul'] }}" class="mt-6 aspect-video w-full rounded-xl object-cover">
    <div class="mt-6 space-y-4 leading-relaxed">
        @foreach (explode("\n\n", $a['isi']) as $blok)
            @if (str_starts_with($blok, '## '))<h2 class="text-xl font-bold">{{ substr($blok, 3, strpos($blok, "\n") ? strpos($blok, "\n") - 3 : null) }}</h2><p>{{ trim(substr($blok, (int) strpos($blok, "\n"))) }}</p>
            @else<p>{!! nl2br(e($blok)) !!}</p>@endif
        @endforeach
    </div>
    <div class="mt-4 flex flex-wrap gap-2">@foreach ($a['tags'] as $t)<a href="{{ route('proto.tag', $t) }}" class="rounded-full bg-emerald-50 px-3 py-1 text-sm text-emerald-800">#{{ $t }}</a>@endforeach</div>
    <section class="mt-10">
        <h2 class="text-xl font-bold">Pertanyaan umum</h2>
        @foreach ($a['faq'] as $f)
            <details class="mt-2 rounded-lg border border-slate-200 p-3"><summary class="cursor-pointer font-medium">{{ $f['q'] }}</summary><p class="mt-2 text-slate-600">{{ $f['a'] }}</p></details>
        @endforeach
    </section>
    <section class="mt-10 flex gap-4 rounded-xl bg-slate-50 p-4">
        <div class="h-14 w-14 shrink-0 rounded-full bg-emerald-200"></div>
        <div><a href="{{ route('proto.penulis', $penulisData['slug']) }}" class="font-bold">{{ $penulisData['nama'] }}</a><p class="text-sm text-slate-600">{{ $penulisData['bio'] }}</p></div>
    </section>
    <h2 class="mt-10 text-xl font-bold">Artikel terkait</h2>
    <div class="mt-4 grid gap-6 sm:grid-cols-2">@foreach ($terkait as $t) @include('proto.partials.kartu', ['a' => $t]) @endforeach</div>
</div>
@endsection

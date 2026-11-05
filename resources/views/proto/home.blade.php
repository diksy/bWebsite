@extends('layouts.publik')
@section('title', $site['nama'].' — '.$site['tagline'])
@section('content')
    <section class="grid gap-6 md:grid-cols-2">
        <img src="{{ $utama['gambar'] }}" alt="{{ $utama['judul'] }}" class="aspect-video w-full rounded-xl object-cover">
        <div class="flex flex-col justify-center">
            <span class="text-xs font-semibold uppercase text-emerald-700">Pilihan redaksi</span>
            <h1 class="mt-2 text-3xl font-bold"><a href="{{ route('proto.artikel', $utama['slug']) }}">{{ $utama['judul'] }}</a></h1>
            <p class="mt-3 text-slate-600">{{ $utama['ringkasan'] }}</p>
            <a href="{{ route('proto.artikel', $utama['slug']) }}" class="mt-4 inline-block text-sm font-semibold text-emerald-700">Baca selengkapnya →</a>
        </div>
    </section>
    <h2 class="mt-12 text-xl font-bold">Terbaru</h2>
    <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($terbaru as $a) @include('proto.partials.kartu', ['a' => $a]) @endforeach
    </div>
    <h2 class="mt-12 text-xl font-bold">Tag populer</h2>
    <div class="mt-3 flex flex-wrap gap-2">
        @foreach ($semuaTags as $t)<a href="{{ route('proto.tag', $t) }}" class="rounded-full bg-emerald-50 px-3 py-1 text-sm text-emerald-800">#{{ $t }}</a>@endforeach
    </div>
@endsection

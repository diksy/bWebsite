<article class="g-card">
    @if ($a->gambar)<a href="{{ $a->url() }}"><img src="{{ Storage::disk('public')->url($a->gambar) }}" alt="{{ $a->gambar_alt ?: $a->judul }}" width="800" height="450" loading="lazy" decoding="async" class="g-img"></a>@endif
    @if ($a->kategori)<a href="{{ route('kategori', $a->kategori->slug) }}" class="g-badge" style="text-decoration:none">{{ $a->kategori->nama }}</a>@endif
    <h3><a href="{{ $a->url() }}" style="text-decoration:none">{{ $a->judul }}</a></h3>
    <p>{{ $a->ringkasan }}</p>
    <p class="g-meta"><time datetime="{{ $a->terbit_pada->toIso8601String() }}">{{ $a->terbit_pada->locale('id')->translatedFormat('d F Y') }}</time> · {{ $a->penulis->name }}</p>
</article>

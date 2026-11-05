<article class="overflow-hidden rounded-xl border border-slate-200">
    @if ($a->gambar)<a href="{{ $a->url() }}"><img src="{{ Storage::disk('public')->url($a->gambar) }}" alt="{{ $a->gambar_alt ?: $a->judul }}" width="800" height="450" loading="lazy" decoding="async" class="aspect-video w-full object-cover"></a>@endif
    <div class="p-4">
        @if ($a->kategori)<a href="{{ route('kategori', $a->kategori->slug) }}" class="text-xs font-semibold uppercase text-emerald-700">{{ $a->kategori->nama }}</a>@endif
        <h3 class="mt-1 text-lg font-bold"><a href="{{ $a->url() }}" class="hover:text-emerald-700">{{ $a->judul }}</a></h3>
        <p class="mt-1 text-sm text-slate-600">{{ $a->ringkasan }}</p>
        <p class="mt-3 text-xs text-slate-400"><time datetime="{{ $a->terbit_pada->toIso8601String() }}">{{ $a->terbit_pada->locale('id')->translatedFormat('d F Y') }}</time> · {{ $a->penulis->name }}</p>
    </div>
</article>

<article class="overflow-hidden rounded-xl border border-slate-200">
    <a href="{{ route('proto.artikel', $a['slug']) }}"><img src="{{ $a['gambar'] }}" alt="{{ $a['judul'] }}" loading="lazy" class="aspect-video w-full object-cover"></a>
    <div class="p-4">
        <a href="{{ route('proto.kategori', $a['kategori']) }}" class="text-xs font-semibold uppercase text-emerald-700">{{ $a['kategori'] }}</a>
        <h3 class="mt-1 text-lg font-bold"><a href="{{ route('proto.artikel', $a['slug']) }}" class="hover:text-emerald-700">{{ $a['judul'] }}</a></h3>
        <p class="mt-1 text-sm text-slate-600">{{ $a['ringkasan'] }}</p>
        <p class="mt-3 text-xs text-slate-400">{{ $a['tanggal'] }}</p>
    </div>
</article>

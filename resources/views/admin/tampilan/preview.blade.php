<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title>Pratinjau {{ $daftarGaya[$gaya] }}</title>
@foreach ($css as $href)<link rel="stylesheet" href="{{ $href }}">
@endforeach
</head>
<body class="g-body">
<div style="background:#111;color:#fff;font:12px/1.4 sans-serif;padding:.4rem 1rem;display:flex;gap:1rem;flex-wrap:wrap">
    <b>Pratinjau</b>
    @foreach ($daftarGaya as $s => $nama)<a href="{{ route('admin.tampilan.preview', [$s, $halaman]) }}" style="color:{{ $s === $gaya ? '#fcd34d' : '#fff' }}">{{ $nama }}</a>@endforeach
    <span>|</span>
    <a href="{{ route('admin.tampilan.preview', [$gaya, 'beranda']) }}" style="color:#fff">Beranda</a><a href="{{ route('admin.tampilan.preview', [$gaya, 'artikel']) }}" style="color:#fff">Artikel</a>
</div>
<header class="g-header"><div class="g-wrap"><a class="g-brand" href="#">{{ \App\Support\Situs::nama() }}</a>
    <nav class="g-nav"><a href="#">Panduan</a><a href="#">SEO</a><a href="#">Teknologi</a></nav></div></header>
<main class="g-main"><div class="g-wrap">
@if ($halaman === 'beranda')
    <p class="g-meta">Artikel terbaru</p>
    <div class="g-grid">
        @foreach ($artikel as $a)
        <article class="g-card"><span class="g-badge">{{ $a['kategori'] }}</span><h2>{{ $a['judul'] }}</h2><p>{{ $a['ringkas'] }}</p><p class="g-meta">{{ $a['penulis'] }} · {{ $a['tanggal'] }}</p></article>
        @endforeach
    </div>
@else
    <article class="g-card g-prose" style="max-width:none"><span class="g-badge">{{ $artikel[0]['kategori'] }}</span>
        <h1 class="g-title">{{ $artikel[0]['judul'] }}</h1><p class="g-meta">{{ $artikel[0]['penulis'] }} · {{ $artikel[0]['tanggal'] }}</p>
        <p>{{ $artikel[0]['ringkas'] }} Mulailah dari satu topik yang Anda kuasai, tulis secara berkala, dan ukur apa yang dibaca orang.</p>
        <h2>Pilih topik yang sempit</h2><p>Topik sempit lebih mudah menang di hasil pencarian dan membangun kepercayaan pembaca.</p>
        <blockquote>Konsistensi mengalahkan inspirasi.</blockquote>
        <a class="g-btn" href="#">Baca artikel terkait</a></article>
@endif
</div></main>
<footer class="g-footer"><div class="g-wrap">© {{ date('Y') }} {{ \App\Support\Situs::nama() }}</div></footer>
</body></html>

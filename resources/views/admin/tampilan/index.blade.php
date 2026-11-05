@extends('layouts.panel')
@section('title', 'Tampilan')
@section('content')
<h1 class="text-2xl font-bold">Tampilan situs</h1>
<p class="mt-1 text-sm text-slate-500">Pilih gaya desain untuk situs publik. Panel admin tidak berubah. <span class="rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-800">Prototype — belum menyimpan</span></p>

<form method="post" action="#" onsubmit="return false" class="mt-5">
    <h2 class="font-bold">Gaya bawaan</h2>
    <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($gaya as $slug => $g)
            <label class="block cursor-pointer rounded-xl bg-white p-4 shadow-sm ring-2 has-[:checked]:ring-emerald-600 ring-transparent">
                <iframe src="{{ route('admin.tampilan.preview', $slug) }}" title="Pratinjau {{ $g['nama'] }}" loading="lazy" class="pointer-events-none h-44 w-full rounded border bg-slate-100"></iframe>
                <div class="mt-3 flex items-center gap-2"><input type="radio" name="gaya" value="{{ $slug }}" @checked($slug === $aktif)>
                    <span class="font-semibold">{{ $g['nama'] }}</span>@if ($slug === 'minimalis')<span class="rounded bg-slate-100 px-2 text-xs">default</span>@endif</div>
                <p class="mt-1 text-xs text-slate-500">{{ $g['info'] }}</p>
                <a href="{{ route('admin.tampilan.preview', $slug) }}" target="_blank" class="mt-2 inline-block text-xs text-emerald-700 hover:underline">Buka pratinjau penuh ↗</a>
            </label>
        @endforeach
    </div>

    <h2 class="mt-8 font-bold">Gaya kustom saya</h2>
    <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($kustom as $k)
            <label class="block cursor-pointer rounded-xl bg-white p-4 shadow-sm ring-2 has-[:checked]:ring-emerald-600 ring-transparent">
                <div class="flex items-center gap-2"><input type="radio" name="gaya" value="{{ $k['slug'] }}"><span class="font-semibold">{{ $k['nama'] }}</span></div>
                <p class="mt-1 text-xs text-slate-500">Diunggah {{ $k['diunggah'] }} · {{ $k['ukuran'] }}</p>
                <div class="mt-2 flex gap-3 text-xs"><a href="{{ route('admin.tampilan.preview', $k['slug']) }}" target="_blank" class="text-emerald-700 hover:underline">Pratinjau ↗</a><button type="button" class="text-red-600 hover:underline">Hapus</button></div>
            </label>
        @endforeach
        @if (! count($kustom))<p class="text-sm text-slate-500">Belum ada gaya kustom.</p>@endif
    </div>
    <button class="mt-6 rounded bg-emerald-700 px-5 py-2 font-semibold text-white hover:bg-emerald-800">Terapkan gaya terpilih</button>
</form>

<section class="mt-10 max-w-2xl space-y-3 rounded-xl bg-white p-5 shadow-sm">
    <h2 class="font-bold">Unggah gaya kustom</h2>
    <ol class="list-decimal space-y-1 pl-5 text-sm text-slate-600">
        <li>Unduh template, lalu ubah nilai variabel (warna, huruf, sudut, bayangan) di editor teks.</li>
        <li>Simpan sebagai berkas <code>.css</code> (maks. 100 KB).</li>
        <li>Unggah di bawah, lihat pratinjau, lalu terapkan.</li>
    </ol>
    <a href="{{ asset('gaya/template-contoh.css') }}" download class="inline-block rounded border border-emerald-700 px-4 py-1.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">⬇ Unduh template-contoh.css</a>
    <form method="post" action="#" onsubmit="return false" enctype="multipart/form-data" class="space-y-3 border-t pt-3">
        <label class="block text-sm">Nama gaya<input name="nama" placeholder="mis. Senja Hangat" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">Berkas CSS<input type="file" name="berkas" accept=".css,text/css" class="mt-1 block w-full text-sm"></label>
        <p class="rounded bg-slate-50 p-2 text-xs text-slate-600">Tidak diizinkan: <code>@import</code>, <code>url()</code> ke situs lain, <code>expression()</code>, <code>&lt;script&gt;</code>. Berkas yang melanggar ditolak dengan pesan alasan.</p>
        <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Unggah</button>
    </form>
</section>
@endsection

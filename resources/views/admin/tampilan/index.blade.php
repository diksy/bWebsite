@extends('layouts.panel')
@section('title', 'Tampilan')
@section('content')
<h1 class="text-2xl font-bold">Tampilan situs</h1>
<p class="mt-1 text-sm text-slate-500">Pilih gaya desain untuk situs publik. Panel admin tidak berubah.</p>

<form method="post" action="{{ route('admin.tampilan.terapkan') }}" class="mt-5">@csrf @method('PUT')
    @error('gaya')<p class="mb-3 rounded bg-red-50 p-2 text-sm text-red-700">{{ $message }}</p>@enderror
    <h2 class="font-bold">Gaya bawaan</h2>
    <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($bawaan as $slug => $g)
            <label class="block cursor-pointer rounded-xl bg-white p-4 shadow-sm ring-2 ring-transparent has-[:checked]:ring-emerald-600">
                <iframe src="{{ route('admin.tampilan.preview', $slug) }}" title="Pratinjau {{ $g['nama'] }}" loading="lazy" class="pointer-events-none h-44 w-full rounded border bg-slate-100"></iframe>
                <div class="mt-3 flex items-center gap-2"><input type="radio" name="gaya" value="{{ $slug }}" @checked($slug === $aktif)>
                    <span class="font-semibold">{{ $g['nama'] }}</span>
                    @if ($slug === \App\Services\Gaya\Gaya::DEFAULT)<span class="rounded bg-slate-100 px-2 text-xs">default</span>@endif
                    @if ($slug === $aktif)<span class="rounded bg-emerald-100 px-2 text-xs text-emerald-800">aktif</span>@endif</div>
                <p class="mt-1 text-xs text-slate-500">{{ $g['info'] }}</p>
                <a href="{{ route('admin.tampilan.preview', $slug) }}" target="_blank" class="mt-2 inline-block text-xs text-emerald-700 hover:underline">Buka pratinjau penuh ↗</a>
            </label>
        @endforeach
    </div>

    <h2 class="mt-8 font-bold">Gaya kustom saya</h2>
    <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($kustom as $k)
            <label class="block cursor-pointer rounded-xl bg-white p-4 shadow-sm ring-2 ring-transparent has-[:checked]:ring-emerald-600">
                <iframe src="{{ route('admin.tampilan.preview', $k->slug) }}" title="Pratinjau {{ $k->nama }}" loading="lazy" class="pointer-events-none h-44 w-full rounded border bg-slate-100"></iframe>
                <div class="mt-3 flex items-center gap-2"><input type="radio" name="gaya" value="{{ $k->slug }}" @checked($k->slug === $aktif)><span class="font-semibold">{{ $k->nama }}</span>
                    @if ($k->slug === $aktif)<span class="rounded bg-emerald-100 px-2 text-xs text-emerald-800">aktif</span>@endif</div>
                <p class="mt-1 text-xs text-slate-500">Diunggah {{ $k->created_at->locale('id')->translatedFormat('d F Y') }} · {{ number_format($k->ukuran / 1024, 1) }} KB</p>
                <div class="mt-2 flex gap-3 text-xs"><a href="{{ route('admin.tampilan.preview', $k->slug) }}" target="_blank" class="text-emerald-700 hover:underline">Pratinjau ↗</a>
                    <button type="submit" form="hapus-{{ $k->id }}" onclick="return confirm('Hapus gaya ini?')" class="text-red-600 hover:underline">Hapus</button></div>
            </label>
        @empty
            <p class="text-sm text-slate-500">Belum ada gaya kustom.</p>
        @endforelse
    </div>
    <button class="mt-6 rounded bg-emerald-700 px-5 py-2 font-semibold text-white hover:bg-emerald-800">Terapkan gaya terpilih</button>
</form>
@foreach ($kustom as $k)<form id="hapus-{{ $k->id }}" method="post" action="{{ route('admin.tampilan.hapus', $k) }}" class="hidden">@csrf @method('DELETE')</form>@endforeach

<section class="mt-10 max-w-2xl space-y-3 rounded-xl bg-white p-5 shadow-sm">
    <h2 class="font-bold">Unggah gaya kustom</h2>
    <ol class="list-decimal space-y-1 pl-5 text-sm text-slate-600">
        <li>Unduh template, lalu ubah nilai variabel (warna, huruf, sudut, bayangan) di editor teks.</li>
        <li>Simpan sebagai berkas <code>.css</code> (maks. 100 KB).</li>
        <li>Unggah di bawah, lihat pratinjau, lalu terapkan.</li>
    </ol>
    <a href="{{ asset('gaya/template-contoh.css') }}" download class="inline-block rounded border border-emerald-700 px-4 py-1.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">⬇ Unduh template-contoh.css</a>
    <form method="post" action="{{ route('admin.tampilan.unggah') }}" enctype="multipart/form-data" class="space-y-3 border-t pt-3">@csrf
        <label class="block text-sm">Nama gaya<input name="nama" value="{{ old('nama') }}" placeholder="mis. Senja Hangat" class="mt-1 w-full rounded border px-3 py-2"></label>
        @error('nama')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
        @error('slug')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
        <label class="block text-sm">Berkas CSS<input type="file" name="berkas" accept=".css,text/css" class="mt-1 block w-full text-sm"></label>
        @error('berkas')<p class="rounded bg-red-50 p-2 text-sm text-red-700">{{ $message }}</p>@enderror
        <p class="rounded bg-slate-50 p-2 text-xs text-slate-600">Tidak diizinkan: <code>@import</code>, <code>url()</code>, <code>expression()</code>, escape backslash, dan karakter <code>&lt;</code>. Berkas yang melanggar ditolak beserta alasannya.</p>
        <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Unggah</button>
    </form>
</section>
@endsection

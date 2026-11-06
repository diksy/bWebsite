@extends('layouts.panel')
@section('title', 'Kategori & tag')
@section('content')
<h1 class="text-2xl font-bold">Kategori &amp; tag</h1>
<div class="mt-4 grid gap-6 lg:grid-cols-2">
    <section class="space-y-3 rounded-xl bg-white p-5 shadow-sm"><h2 class="font-bold">Kategori</h2>
        <ul class="divide-y text-sm">@foreach ($kategori as $k)
            <li class="py-2"><form method="post" action="{{ route('admin.kategori.update', $k) }}" class="flex flex-wrap items-center gap-2">@csrf @method('PUT')
                <input name="nama" value="{{ $k->nama }}" class="w-32 rounded border px-2 py-1"><input name="slug" value="{{ $k->slug }}" class="w-32 rounded border px-2 py-1"><input name="deskripsi" value="{{ $k->deskripsi }}" placeholder="Deskripsi" class="min-w-0 flex-1 rounded border px-2 py-1">
                <button class="text-emerald-700">Simpan</button></form>
                <form method="post" action="{{ route('admin.kategori.destroy', $k) }}" data-konfirmasi="Hapus kategori?">@csrf @method('DELETE')<button class="text-xs text-red-600">Hapus</button></form></li>
        @endforeach</ul>
        <form method="post" action="{{ route('admin.kategori.store') }}" class="flex flex-wrap gap-2 border-t pt-3 text-sm">@csrf
            <input name="nama" placeholder="Nama kategori" required class="rounded border px-2 py-1"><input name="deskripsi" placeholder="Deskripsi (opsional)" class="min-w-0 flex-1 rounded border px-2 py-1"><button class="rounded bg-emerald-700 px-3 py-1 text-white">Tambah</button></form></section>
    <section class="space-y-3 rounded-xl bg-white p-5 shadow-sm"><h2 class="font-bold">Tag</h2>
        <div class="flex flex-wrap gap-2 text-sm">@foreach ($tag as $t)
            <form method="post" action="{{ route('admin.tag.destroy', $t) }}" class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-emerald-800">#{{ $t->nama }} @csrf @method('DELETE')<button title="Hapus" class="text-red-500">×</button></form>
        @endforeach</div>
        <form method="post" action="{{ route('admin.tag.store') }}" class="flex gap-2 border-t pt-3 text-sm">@csrf<input name="nama" placeholder="Tag baru" required class="rounded border px-2 py-1"><button class="rounded bg-emerald-700 px-3 py-1 text-white">Tambah</button></form></section>
</div>
@endsection

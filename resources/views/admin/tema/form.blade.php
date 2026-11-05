@extends('layouts.panel')
@section('title', $t->exists ? 'Sunting tema' : 'Tema baru')
@section('content')
<h1 class="text-2xl font-bold">{{ $t->exists ? 'Sunting tema' : 'Tema baru' }}</h1>
<form method="post" action="{{ $t->exists ? route('admin.tema.update', $t) : route('admin.tema.store') }}" class="mt-4 max-w-xl space-y-3 rounded-xl bg-white p-5 shadow-sm">@csrf @if ($t->exists) @method('PUT') @endif
    <label class="block text-sm">Tema<input name="nama" value="{{ old('nama', $t->nama) }}" required class="mt-1 w-full rounded border px-3 py-2"></label>
    <label class="block text-sm">Kata kunci <span class="text-slate-400">(pisahkan dengan koma)</span><textarea name="kata_kunci" rows="2" required class="mt-1 w-full rounded border px-3 py-2">{{ old('kata_kunci', $t->kata_kunci) }}</textarea></label>
    <label class="block text-sm">Frekuensi<select name="frekuensi" class="mt-1 w-full rounded border px-3 py-2">@foreach (\App\Models\Tema::LABEL as $k => $l)<option value="{{ $k }}" @selected(old('frekuensi', $t->frekuensi) === $k)>{{ $l }}</option>@endforeach</select></label>
    <label class="block text-sm">Kategori tujuan<select name="kategori_id" class="mt-1 w-full rounded border px-3 py-2"><option value="">— tanpa kategori —</option>@foreach ($daftarKategori as $k)<option value="{{ $k->id }}" @selected((int) old('kategori_id', $t->kategori_id) === $k->id)>{{ $k->nama }}</option>@endforeach</select></label>
    <label class="block text-sm">Penulis tujuan<select name="user_id" class="mt-1 w-full rounded border px-3 py-2">@foreach ($daftarPenulis as $p)<option value="{{ $p->id }}" @selected((int) old('user_id', $t->user_id) === $p->id)>{{ $p->name }}</option>@endforeach</select></label>
    <label class="block text-sm">Batas draf per minggu<input type="number" name="batas_per_minggu" min="1" max="20" value="{{ old('batas_per_minggu', $t->batas_per_minggu) }}" class="mt-1 w-full rounded border px-3 py-2"></label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $t->aktif))> Aktif</label>
    <div class="flex gap-2"><button class="rounded bg-emerald-700 px-5 py-2 font-medium text-white">Simpan</button><a href="{{ route('admin.tema.index') }}" class="rounded border px-5 py-2">Batal</a></div>
</form>
@endsection

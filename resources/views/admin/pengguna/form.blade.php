@extends('layouts.panel')
@section('title', $u->exists ? 'Sunting pengguna' : 'Pengguna baru')
@section('content')
<h1 class="text-2xl font-bold">{{ $u->exists ? 'Sunting pengguna' : 'Pengguna baru' }}</h1>
<form method="post" action="{{ $u->exists ? route('admin.pengguna.update', $u) : route('admin.pengguna.store') }}" class="mt-4 max-w-xl space-y-3 rounded-xl bg-white p-5 shadow-sm">
    @csrf @if ($u->exists) @method('PUT') @endif
    <label class="block text-sm">Nama<input name="name" value="{{ old('name', $u->name) }}" required class="mt-1 w-full rounded border px-3 py-2"></label>
    <label class="block text-sm">Email <span class="text-slate-400">(dipakai mencocokkan login SSO)</span><input name="email" type="email" value="{{ old('email', $u->email) }}" required class="mt-1 w-full rounded border px-3 py-2"></label>
    <label class="block text-sm">Peran<select name="peran" class="mt-1 w-full rounded border px-3 py-2">@foreach (['penulis' => 'Penulis', 'admin' => 'Admin'] as $k => $l)<option value="{{ $k }}" @selected(old('peran', $u->peran) === $k)>{{ $l }}</option>@endforeach</select></label>
    <label class="block text-sm">Bio penulis <span class="text-slate-400">(tampil di halaman penulis)</span><textarea name="bio" rows="3" class="mt-1 w-full rounded border px-3 py-2">{{ old('bio', $u->bio) }}</textarea></label>
    <label class="block text-sm">Kata sandi <span class="text-slate-400">({{ $u->exists ? 'kosongkan bila tidak diubah' : 'kosongkan untuk akun tanpa login/SSO saja' }})</span><input name="password" type="password" autocomplete="new-password" class="mt-1 w-full rounded border px-3 py-2"></label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $u->is_active))> Aktif</label>
    <div class="flex gap-2"><button class="rounded bg-emerald-700 px-5 py-2 font-medium text-white">Simpan</button><a href="{{ route('admin.pengguna.index') }}" class="rounded border px-5 py-2">Batal</a></div>
</form>
@endsection

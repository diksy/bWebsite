@extends('layouts.admin')
@section('title', $u ? 'Sunting pengguna' : 'Pengguna baru')
@section('content')
<h1 class="text-2xl font-bold">{{ $u ? 'Sunting pengguna' : 'Pengguna baru' }}</h1>
<form class="mt-4 max-w-xl space-y-3 rounded-xl bg-white p-5 shadow-sm">
    <label class="block text-sm">Nama<input value="{{ $u['nama'] ?? '' }}" class="mt-1 w-full rounded border px-3 py-2"></label>
    <label class="block text-sm">Email <span class="text-slate-400">(dipakai mencocokkan login SSO)</span><input type="email" value="{{ $u['email'] ?? '' }}" class="mt-1 w-full rounded border px-3 py-2"></label>
    <label class="block text-sm">Peran<select class="mt-1 w-full rounded border px-3 py-2"><option @selected(($u['peran'] ?? '') === 'Penulis')>Penulis</option><option @selected(($u['peran'] ?? '') === 'Admin')>Admin</option></select></label>
    <label class="block text-sm">Bio penulis <span class="text-slate-400">(tampil di halaman penulis)</span><textarea rows="3" class="mt-1 w-full rounded border px-3 py-2"></textarea></label>
    <label class="block text-sm">Kata sandi {{ $u ? '(kosongkan bila tidak diubah)' : '' }}<input type="password" class="mt-1 w-full rounded border px-3 py-2"></label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" @checked($u['aktif'] ?? true)> Aktif</label>
    <div class="flex gap-2"><button type="button" class="rounded bg-emerald-700 px-5 py-2 font-medium text-white">Simpan</button><a href="{{ route('proto.admin.pengguna') }}" class="rounded border px-5 py-2">Batal</a></div>
</form>
@endsection

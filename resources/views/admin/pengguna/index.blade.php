@extends('layouts.panel')
@section('title', 'Pengguna')
@section('content')
<div class="flex items-center justify-between"><h1 class="text-2xl font-bold">Pengguna</h1><a href="{{ route('admin.pengguna.create') }}" class="rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">+ Pengguna baru</a></div>
<div class="mt-4 overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-left text-sm">
    <thead class="border-b text-slate-500"><tr><th class="p-3">Nama</th><th>Email</th><th>Peran</th><th>Status</th><th>Cara masuk</th><th></th></tr></thead>
    <tbody class="divide-y">@foreach ($pengguna as $u)
        <tr><td class="p-3 font-medium">{{ $u->name }}</td><td>{{ $u->email }}</td>
        <td><span class="rounded px-2 py-0.5 text-xs {{ $u->isAdmin() ? 'bg-violet-100 text-violet-800' : 'bg-slate-100 text-slate-700' }}">{{ ucfirst($u->peran) }}</span></td>
        <td>{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</td>
        <td>{{ $u->password === null ? ($u->sso_subject ? 'SSO' : 'Tanpa login') : 'Kata sandi' }}</td>
        <td class="whitespace-nowrap"><a class="text-emerald-700" href="{{ route('admin.pengguna.edit', $u) }}">Sunting</a>
            <form method="post" action="{{ route('admin.pengguna.destroy', $u) }}" class="inline" data-konfirmasi="Hapus pengguna ini?">@csrf @method('DELETE')
                @if ($u->artikel_count > 0)<select name="pindah_ke" class="rounded border text-xs" title="Pindahkan {{ $u->artikel_count }} artikel ke"><option value="">Pindahkan {{ $u->artikel_count }} artikel ke…</option>@foreach ($pengguna->where('is_active', true)->where('id', '!=', $u->id) as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select>@endif
                <button class="ml-2 text-red-600">Hapus</button></form></td></tr>
    @endforeach</tbody></table></div>
@endsection

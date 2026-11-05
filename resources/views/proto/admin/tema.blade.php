@extends('layouts.admin')
@section('title', 'Tema terjadwal')
@section('content')
<h1 class="text-2xl font-bold">Tema terjadwal</h1>
<div class="mt-4 grid gap-6 lg:grid-cols-3">
    <div class="space-y-3 lg:col-span-2">@foreach ($tema as $t)
        <div class="rounded-xl bg-white p-4 shadow-sm"><div class="flex items-start justify-between"><div><b>{{ $t['tema'] }}</b><p class="text-sm text-slate-500">{{ $t['kata_kunci'] }}</p></div>
            <span class="rounded px-2 py-0.5 text-xs {{ $t['aktif'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">{{ $t['aktif'] ? 'Aktif' : 'Jeda' }}</span></div>
            <p class="mt-2 text-sm text-slate-600">{{ $t['frekuensi'] }} · kategori {{ $t['kategori'] }} · penulis {{ $t['penulis'] }} · maks {{ $t['batas'] }} draf/minggu</p></div>
    @endforeach</div>
    <form class="space-y-3 rounded-xl bg-white p-4 shadow-sm"><b>Tema baru</b>
        <input placeholder="Tema" class="w-full rounded border px-3 py-2 text-sm"><input placeholder="Kata kunci (pisah koma)" class="w-full rounded border px-3 py-2 text-sm">
        <select class="w-full rounded border px-3 py-2 text-sm"><option>Harian</option><option>Mingguan</option><option>Bulanan</option></select>
        <select class="w-full rounded border px-3 py-2 text-sm">@foreach ($kategori as $k)<option>{{ $k['nama'] }}</option>@endforeach</select>
        <select class="w-full rounded border px-3 py-2 text-sm">@foreach ($penulis as $p)<option>{{ $p['nama'] }}</option>@endforeach</select>
        <label class="block text-sm">Batas draf/minggu<input type="number" value="3" class="mt-1 w-full rounded border px-3 py-2"></label>
        <button type="button" class="w-full rounded bg-emerald-700 py-2 font-medium text-white">Simpan tema</button></form>
</div>
@endsection

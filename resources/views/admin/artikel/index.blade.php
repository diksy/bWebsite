@extends('layouts.panel')
@section('title', 'Artikel')
@section('content')
<div class="flex items-center justify-between"><h1 class="text-2xl font-bold">Artikel</h1><a href="{{ route('admin.artikel.create') }}" class="rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">+ Artikel baru</a></div>
<div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
    @foreach ([null => 'Semua', 'draf' => 'Draf', 'terjadwal' => 'Terjadwal', 'terbit' => 'Terbit'] as $k => $l)
        <a href="{{ route('admin.artikel.index', array_filter(['status' => $k, 'q' => $cari])) }}" class="rounded-full px-3 py-1 {{ $status == $k ? 'bg-slate-800 text-white' : 'bg-white shadow-sm' }}">{{ $l }}</a>
    @endforeach
    <form class="ml-auto"><input type="hidden" name="status" value="{{ $status }}"><input name="q" value="{{ $cari }}" type="search" placeholder="Cari judul…" class="rounded-full border px-3 py-1"></form>
</div>
<div class="mt-4 overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-left text-sm">
    <thead class="border-b text-slate-500"><tr><th class="p-3">Judul</th><th>Penulis</th><th>Status</th><th>Terbit</th><th></th></tr></thead>
    <tbody class="divide-y">@forelse ($artikel as $a)
        <tr><td class="p-3 font-medium">{{ $a->judul }} @if($a->dibuat_ai)<span class="ml-1 rounded bg-violet-100 px-1.5 text-xs text-violet-700">AI</span>@endif</td><td>{{ $a->penulis->name }}</td>
        <td><span class="rounded px-2 py-0.5 text-xs {{ ['draf'=>'bg-amber-100 text-amber-800','terjadwal'=>'bg-sky-100 text-sky-800','terbit'=>'bg-emerald-100 text-emerald-800'][$a->status] }}">{{ ucfirst($a->status) }}</span></td>
        <td>{{ $a->terbit_pada?->timezone(config('app.timezone'))->format('d M Y H:i') ?? '—' }}</td>
        <td class="whitespace-nowrap"><a class="text-emerald-700" href="{{ route('admin.artikel.edit', $a) }}">Sunting</a>
            <form method="post" action="{{ route('admin.artikel.destroy', $a) }}" class="inline" onsubmit="return confirm('Hapus artikel ini?')">@csrf @method('DELETE')<button class="ml-2 text-red-600">Hapus</button></form></td></tr>
    @empty<tr><td colspan="5" class="p-6 text-center text-slate-500">Belum ada artikel.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $artikel->links() }}</div>
@endsection

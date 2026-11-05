@extends('layouts.admin')
@section('title', 'Artikel')
@section('content')
    <div class="flex items-center justify-between"><h1 class="text-2xl font-bold">Artikel</h1><a href="{{ route('proto.admin.editor', 1) }}" class="rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">+ Artikel baru</a></div>
    <div class="mt-4 flex flex-wrap gap-2 text-sm">
        @foreach ([null => 'Semua', 'draf' => 'Draf', 'terjadwal' => 'Terjadwal', 'terbit' => 'Terbit'] as $k => $l)
            <a href="{{ route('proto.admin.artikel', $k ? ['status' => $k] : []) }}" class="rounded-full px-3 py-1 {{ $status == $k ? 'bg-slate-800 text-white' : 'bg-white shadow-sm' }}">{{ $l }}</a>
        @endforeach
    </div>
    <div class="mt-4 overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-left text-sm">
        <thead class="border-b text-slate-500"><tr><th class="p-3">Judul</th><th>Status</th><th>Skor SEO</th><th>Tanggal</th><th></th></tr></thead>
        <tbody class="divide-y">@foreach ($artikel as $a)
            <tr><td class="p-3 font-medium">{{ $a['judul'] }} @if($a['sumber_ai'])<span class="ml-1 rounded bg-violet-100 px-1.5 text-xs text-violet-700">AI</span>@endif</td>
            <td><span class="rounded px-2 py-0.5 text-xs {{ ['draf'=>'bg-amber-100 text-amber-800','terjadwal'=>'bg-sky-100 text-sky-800','terbit'=>'bg-emerald-100 text-emerald-800'][$a['status']] }}">{{ ucfirst($a['status']) }}</span></td>
            <td>{{ $a['skor'] }}/100</td><td>{{ $a['tanggal'] }}</td><td><a class="text-emerald-700" href="{{ route('proto.admin.editor', $a['id']) }}">Sunting</a></td></tr>
        @endforeach</tbody></table></div>
@endsection

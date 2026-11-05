@extends('layouts.panel')
@section('title', 'Tema terjadwal')
@section('content')
<div class="flex items-center justify-between"><h1 class="text-2xl font-bold">Tema terjadwal</h1><a href="{{ route('admin.tema.create') }}" class="rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">+ Tema baru</a></div>
<p class="mt-1 text-sm text-slate-500">Penulis AI membuat <b>draf</b> dari tema ini sesuai frekuensi. Tidak ada auto-publish — setiap draf harus ditinjau dan diterbitkan manual.</p>
<div class="mt-4 space-y-3">@forelse ($tema as $t)
    <div class="rounded-xl bg-white p-4 shadow-sm"><div class="flex items-start justify-between gap-3"><div><b>{{ $t->nama }}</b><p class="text-sm text-slate-500">{{ $t->kata_kunci }}</p></div>
        <span class="rounded px-2 py-0.5 text-xs {{ $t->aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">{{ $t->aktif ? 'Aktif' : 'Jeda' }}</span></div>
        <p class="mt-2 text-sm text-slate-600">{{ \App\Models\Tema::LABEL[$t->frekuensi] ?? $t->frekuensi }} · kategori {{ $t->kategori?->nama ?? '—' }} · penulis {{ $t->penulis->name }} · maks {{ $t->batas_per_minggu }} draf/minggu (terpakai minggu ini: {{ $t->draftMingguIni() }})
            @if ($t->terakhir_jalan) · terakhir jalan {{ $t->terakhir_jalan->timezone(config('app.timezone'))->format('d M H:i') }}@endif</p>
        <div class="mt-2 flex gap-3 text-sm">
            @if (Route::has('admin.tema.jalankan'))<form method="post" action="{{ route('admin.tema.jalankan', $t) }}">@csrf<button class="text-emerald-700">Jalankan sekarang</button></form>@endif
            <a class="text-emerald-700" href="{{ route('admin.tema.edit', $t) }}">Sunting</a>
            <form method="post" action="{{ route('admin.tema.destroy', $t) }}" onsubmit="return confirm('Hapus tema? Artikel hasilnya tetap ada.')">@csrf @method('DELETE')<button class="text-red-600">Hapus</button></form></div></div>
@empty<p class="rounded-xl bg-white p-6 text-center text-slate-500 shadow-sm">Belum ada tema.</p>@endforelse</div>
@endsection

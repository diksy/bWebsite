@extends('layouts.admin')
@section('title', 'Sunting artikel')
@section('content')
<form class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-4 lg:col-span-2">
        <h1 class="text-2xl font-bold">Sunting artikel</h1>
        <input value="{{ $a['judul'] }}" class="w-full rounded border border-slate-300 px-3 py-2 text-lg font-semibold">
        <textarea rows="3" class="w-full rounded border border-slate-300 px-3 py-2 text-sm" placeholder="Meta description">{{ $a['ringkasan'] }}</textarea>
        <div class="rounded border border-slate-300 bg-white"><div class="flex gap-3 border-b px-3 py-1.5 text-sm text-slate-500"><b>B</b><i>I</i><span>H2</span><span>Link</span><span>Gambar</span><span class="ml-auto">Markdown</span></div>
            <textarea rows="14" class="w-full px-3 py-2 font-mono text-sm">{{ $a['isi'] }}</textarea></div>
        <details class="rounded border border-slate-200 bg-white p-3"><summary class="cursor-pointer font-medium">FAQ ({{ count($a['faq']) }})</summary>
            @foreach ($a['faq'] as $f)<div class="mt-2 space-y-1"><input value="{{ $f['q'] }}" class="w-full rounded border px-2 py-1 text-sm"><input value="{{ $f['a'] }}" class="w-full rounded border px-2 py-1 text-sm"></div>@endforeach</details>
        @if ($a['sumber_ai'])<div class="rounded border border-violet-200 bg-violet-50 p-3 text-sm"><b>Sumber riset AI</b><ul class="mt-1 list-disc pl-5"><li>https://contoh.com/panduan-seo</li><li>https://contoh.id/riset-konten</li></ul></div>@endif
    </div>
    <aside class="space-y-4">
        <div class="rounded-xl bg-white p-4 shadow-sm"><div class="flex items-baseline justify-between"><b>Skor SEO</b><span class="text-3xl font-bold text-amber-600">{{ $a['skor'] }}</span></div>
            <ul class="mt-3 space-y-1.5 text-sm">@foreach ($cek as $c)<li class="{{ $c['ok'] ? 'text-emerald-700' : 'text-red-600' }}">{{ $c['ok'] ? '✓' : '✗' }} {{ $c['teks'] }}</li>@endforeach</ul></div>
        <div class="rounded-xl bg-white p-4 shadow-sm"><b>Saran link internal</b><ul class="mt-2 space-y-1 text-sm">@foreach ($saranLink as $s)<li class="flex justify-between gap-2"><span>{{ $s['judul'] }}</span><button type="button" class="text-emerald-700">Sisipkan</button></li>@endforeach</ul></div>
        <div class="space-y-3 rounded-xl bg-white p-4 shadow-sm"><b>Gambar &amp; terbit</b>
            <label class="block rounded border-2 border-dashed border-slate-300 p-4 text-center text-sm text-slate-500">Unggah gambar (otomatis jadi WebP)<input type="file" class="hidden"></label>
            <label class="block text-sm">Status<select class="mt-1 w-full rounded border px-2 py-1.5"><option>Draf</option><option>Terjadwal</option><option>Terbit</option></select></label>
            <label class="block text-sm">Jadwal terbit<input type="datetime-local" class="mt-1 w-full rounded border px-2 py-1.5"></label>
            <button type="button" class="w-full rounded bg-emerald-700 py-2 font-medium text-white">Simpan</button></div>
    </aside>
</form>
@endsection

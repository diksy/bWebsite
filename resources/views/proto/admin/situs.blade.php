@extends('layouts.admin')
@section('title', 'Pengaturan situs')
@section('content')
<h1 class="text-2xl font-bold">Pengaturan situs</h1>
<form class="mt-4 max-w-2xl space-y-6" x-data>
    <section class="space-y-3 rounded-xl bg-white p-5 shadow-sm"><h2 class="font-bold">Path panel admin</h2>
        <label class="block text-sm">Path login &amp; panel admin
            <div class="mt-1 flex items-center rounded border"><span class="px-3 text-slate-400">https://storika.id/</span><input id="p-admin" value="admin" class="w-full px-2 py-2 outline-none" oninput="document.getElementById('v-admin').textContent=this.value||'admin'"></div></label>
        <p class="text-sm text-slate-500">Contoh: <code>/<span id="v-admin">admin</span>/masuk</code>. Hanya huruf kecil, angka, tanda hubung. Tidak boleh bentrok dengan path publik atau nama yang dicadangkan (mis. cari, tag, kategori, penulis, feed, sitemap.xml).</p>
        <p class="rounded bg-amber-50 p-2 text-xs text-amber-800">Setelah diganti, panel pindah ke path baru dan path lama tidak aktif lagi. Simpan path baru sebelum keluar.</p></section>
    <button type="button" class="rounded bg-emerald-700 px-5 py-2 font-medium text-white">Simpan</button>
</form>
@endsection

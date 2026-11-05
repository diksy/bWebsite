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
    <section class="space-y-3 rounded-xl bg-white p-5 shadow-sm"><h2 class="font-bold">Path artikel</h2>
        <label class="block text-sm">Awalan URL artikel
            <div class="mt-1 flex items-center rounded border"><span class="px-3 text-slate-400">https://storika.id/</span><input value="artikel" class="w-full px-2 py-2 outline-none" oninput="document.getElementById('v-art').textContent=this.value||'artikel'"></div></label>
        <p class="text-sm text-slate-500">Contoh: <code>/<span id="v-art">artikel</span>/panduan-seo-untuk-blog-baru</code>. Boleh dikosongkan agar artikel langsung di <code>/judul-artikel</code>.</p>
        <p class="rounded bg-amber-50 p-2 text-xs text-amber-800">URL lama otomatis dialihkan (301) ke path baru; canonical, sitemap, RSS, dan JSON-LD ikut berubah.</p></section>
    <section class="space-y-3 rounded-xl bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><h2 class="font-bold">Login SSO (opsional)</h2><label class="flex items-center gap-2 text-sm"><input type="checkbox" checked> Aktifkan</label></div>
        <p class="text-sm text-slate-500">Integrasi BikininSSO (OIDC, Authorization Code + PKCE). Login email &amp; kata sandi tetap tersedia.</p>
        <label class="block text-sm">Base URL SSO<input value="https://sso.bikinin.id" class="mt-1 w-full rounded border px-3 py-2"></label>
        <div class="grid gap-3 sm:grid-cols-2"><label class="block text-sm">Client ID<input value="blog-ai-storika" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">Client secret <span class="text-slate-400">(terenkripsi)</span><input type="password" value="••••••••••••" class="mt-1 w-full rounded border px-3 py-2"></label></div>
        <div class="rounded bg-slate-50 p-3 text-xs text-slate-600"><b>Daftarkan di admin SSO:</b><br>Redirect login: <code>https://storika.id/sso/callback</code><br>Post-logout: <code>https://storika.id/{{ 'admin' }}/masuk</code><br>Back-channel logout: <code>https://storika.id/sso/backchannel-logout</code></div>
        <div class="rounded bg-sky-50 p-3 text-xs text-sky-900">Pengguna SSO hanya bisa masuk bila emailnya sudah didaftarkan di menu <a class="underline" href="{{ route('proto.admin.pengguna') }}">Pengguna</a>. Akun tidak dibuat otomatis; email yang belum terdaftar atau nonaktif ditolak.</div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox"> Wajib SSO (sembunyikan login kata sandi)</label>
        <div class="flex items-center gap-3"><button type="button" class="rounded border border-emerald-700 px-4 py-2 text-sm text-emerald-700">Uji koneksi (discovery)</button><span class="text-sm text-emerald-700">✓ Discovery &amp; JWKS terbaca (contoh hasil)</span></div></section>
    <button type="button" class="rounded bg-emerald-700 px-5 py-2 font-medium text-white">Simpan</button>
</form>
@endsection

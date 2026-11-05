@extends('layouts.panel')
@section('title', 'Pengaturan situs')
@section('content')
<h1 class="text-2xl font-bold">Pengaturan situs</h1>
<form method="post" action="{{ route('admin.pengaturan.situs.simpan') }}" class="mt-4 max-w-2xl space-y-6">@csrf @method('PUT')
    <section class="space-y-3 rounded-xl bg-white p-5 shadow-sm"><h2 class="font-bold">Path panel admin</h2>
        <div class="flex items-center rounded border"><span class="px-3 text-slate-400">{{ url('/') }}/</span><input name="admin_path" value="{{ old('admin_path', $admin_path) }}" class="w-full px-2 py-2 outline-none"></div>
        <p class="text-sm text-slate-500">Huruf kecil, angka, tanda hubung. Tidak boleh memakai nama yang dicadangkan (cari, tag, kategori, penulis, feed, sitemap.xml, dst.).</p>
        <p class="rounded bg-amber-50 p-2 text-xs text-amber-800">Setelah diganti, panel pindah ke path baru dan path lama tidak aktif lagi.</p></section>
    <section class="space-y-3 rounded-xl bg-white p-5 shadow-sm"><h2 class="font-bold">Path artikel</h2>
        <div class="flex items-center rounded border"><span class="px-3 text-slate-400">{{ url('/') }}/</span><input name="artikel_path" value="{{ old('artikel_path', $artikel_path) }}" class="w-full px-2 py-2 outline-none"></div>
        <p class="text-sm text-slate-500">Kosongkan agar artikel langsung di <code>/judul-artikel</code>. URL lama dialihkan (301) ke path baru; canonical, sitemap, RSS, dan JSON-LD ikut berubah.</p></section>
    <section class="space-y-3 rounded-xl bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><h2 class="font-bold">Login SSO (opsional)</h2><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="sso_aktif" value="1" @checked(old('sso_aktif', $sso['aktif']))> Aktifkan</label></div>
        <p class="text-sm text-slate-500">BikininSSO (OIDC, Authorization Code + PKCE). Hanya pengguna yang sudah terdaftar &amp; aktif (cocok lewat email) yang bisa masuk; akun tidak dibuat otomatis.</p>
        <label class="block text-sm">Base URL SSO<input name="sso_base_url" value="{{ old('sso_base_url', $sso['base_url']) }}" placeholder="https://sso.bikinin.id" class="mt-1 w-full rounded border px-3 py-2"></label>
        <div class="grid gap-3 sm:grid-cols-2"><label class="block text-sm">Client ID<input name="sso_client_id" value="{{ old('sso_client_id', $sso['client_id']) }}" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">Client secret <span class="text-slate-400">(terenkripsi{{ $sso['ada_secret'] ? '; tersimpan, kosongkan bila tidak diubah' : '' }})</span><input name="sso_client_secret" type="password" autocomplete="new-password" class="mt-1 w-full rounded border px-3 py-2"></label></div>
        <div class="rounded bg-slate-50 p-3 text-xs text-slate-600"><b>Daftarkan di admin SSO:</b><br>Redirect login: <code>{{ $callback }}</code><br>Post-logout: <code>{{ $postLogout }}</code><br>Back-channel logout: <code>{{ $backchannel }}</code></div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="sso_wajib" value="1" @checked(old('sso_wajib', $sso['wajib']))> Wajib SSO (login kata sandi dinonaktifkan)</label></section>
    <button class="rounded bg-emerald-700 px-5 py-2 font-medium text-white">Simpan</button>
</form>
<form method="post" action="{{ route('admin.pengaturan.situs.uji-sso') }}" class="mt-3">@csrf<button class="rounded border border-emerald-700 px-4 py-2 text-sm text-emerald-700">Uji koneksi SSO (discovery &amp; JWKS)</button></form>
@endsection

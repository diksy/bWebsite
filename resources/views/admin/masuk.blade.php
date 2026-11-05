<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Masuk — {{ config('app.name') }}</title>@vite(['resources/css/app.css'])</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 p-4">
<div class="w-full max-w-sm space-y-4 rounded-xl bg-white p-6 shadow">
    <h1 class="text-xl font-bold">Masuk ke {{ config('app.name') }}</h1>
    @if ($errors->any())<div class="rounded bg-red-50 p-2 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    @unless ($ssoWajib)
    <form method="post" action="{{ route('admin.masuk.proses') }}" class="space-y-4">@csrf
        <label class="block text-sm font-medium">Email<input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
        <label class="block text-sm font-medium">Kata sandi<input name="password" type="password" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="ingat" value="1"> Ingat saya</label>
        <button class="w-full rounded bg-emerald-700 py-2 font-medium text-white">Masuk</button>
    </form>
    @endunless
    @if ($ssoAktif)
        @unless ($ssoWajib)<div class="flex items-center gap-2 text-xs text-slate-400"><hr class="flex-1"> atau <hr class="flex-1"></div>@endunless
        <a href="{{ Route::has('admin.sso.mulai') ? route('admin.sso.mulai') : '#' }}" class="block w-full rounded border border-slate-300 py-2 text-center font-medium">Masuk dengan SSO Bikinin</a>
    @endif
</div></body></html>

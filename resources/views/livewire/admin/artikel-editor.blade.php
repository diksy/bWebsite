<form wire:submit="simpan" class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-4 lg:col-span-2">
        <h1 class="text-2xl font-bold">{{ $artikelId ? 'Sunting artikel' : 'Artikel baru' }} @if($dibuatAi)<span class="ml-2 rounded bg-violet-100 px-2 py-0.5 text-xs font-normal text-violet-700">Draf AI — tinjau sebelum terbit</span>@endif</h1>
        @if (session('status'))<div class="rounded bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="rounded bg-red-50 p-3 text-sm text-red-700">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
        <input wire:model.blur="judul" placeholder="Judul" class="w-full rounded border border-slate-300 px-3 py-2 text-lg font-semibold">
        <div class="flex items-center rounded border border-slate-300 bg-white text-sm"><span class="px-3 text-slate-400">slug:</span><input wire:model="slug" placeholder="otomatis dari judul" class="w-full px-2 py-2 outline-none"></div>
        <textarea wire:model.live.debounce.500ms="ringkasan" rows="2" maxlength="320" placeholder="Meta description (±150 karakter)" class="w-full rounded border border-slate-300 px-3 py-2 text-sm"></textarea>
        <input wire:model.live.debounce.500ms="kata_kunci" placeholder="Kata kunci utama" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        <div class="rounded border border-slate-300 bg-white"><div class="border-b px-3 py-1.5 text-xs text-slate-500">Markdown: <code>## Subjudul</code>, <code>**tebal**</code>, <code>[teks](url)</code>, <code>![alt](url)</code></div>
            <textarea wire:model.live.debounce.700ms="isi" rows="18" class="w-full px-3 py-2 font-mono text-sm"></textarea></div>
        <section class="space-y-2 rounded border border-slate-200 bg-white p-3"><div class="flex items-center justify-between"><b>FAQ</b><button type="button" wire:click="tambahFaq" class="text-sm text-emerald-700">+ Tambah</button></div>
            @foreach ($faq as $i => $f)
                <div class="space-y-1 border-t pt-2" wire:key="faq-{{ $i }}"><input wire:model="faq.{{ $i }}.q" placeholder="Pertanyaan" class="w-full rounded border px-2 py-1 text-sm"><textarea wire:model="faq.{{ $i }}.a" rows="2" placeholder="Jawaban" class="w-full rounded border px-2 py-1 text-sm"></textarea><button type="button" wire:click="hapusFaq({{ $i }})" class="text-xs text-red-600">Hapus</button></div>
            @endforeach</section>
        @if ($sumber)<div class="rounded border border-violet-200 bg-violet-50 p-3 text-sm"><b>Sumber riset AI</b><ul class="mt-1 list-disc pl-5">@foreach ($sumber as $s)<li><a class="underline" href="{{ $s['url'] }}" target="_blank" rel="noopener nofollow">{{ $s['judul'] ?? $s['url'] }}</a></li>@endforeach</ul></div>@endif
    </div>
    <aside class="space-y-4">
        <div class="space-y-3 rounded-xl bg-white p-4 shadow-sm"><b>Terbit</b>
            <label class="block text-sm">Status<select wire:model.live="status" class="mt-1 w-full rounded border px-2 py-1.5"><option value="draf">Draf</option><option value="terjadwal">Terjadwal</option><option value="terbit">Terbit</option></select></label>
            @if ($status !== 'draf')<label class="block text-sm">{{ $status === 'terjadwal' ? 'Jadwal terbit' : 'Tanggal terbit' }}<input type="datetime-local" wire:model="terbit_pada" class="mt-1 w-full rounded border px-2 py-1.5"></label>@endif
            <label class="block text-sm">Kategori<select wire:model="kategori_id" class="mt-1 w-full rounded border px-2 py-1.5"><option value="">— tanpa kategori —</option>@foreach ($daftarKategori as $k)<option value="{{ $k->id }}">{{ $k->nama }}</option>@endforeach</select></label>
            @if ($daftarPenulis->isNotEmpty())<label class="block text-sm">Penulis<select wire:model="userId" class="mt-1 w-full rounded border px-2 py-1.5">@foreach ($daftarPenulis as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></label>@endif
            <div class="text-sm">Tag<div class="mt-1 flex flex-wrap gap-2">@foreach ($daftarTag as $t)<label class="flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5"><input type="checkbox" wire:model="tagIds" value="{{ $t->id }}"> #{{ $t->nama }}</label>@endforeach</div></div>
            <button class="w-full rounded bg-emerald-700 py-2 font-medium text-white">Simpan</button></div>
        <div class="space-y-3 rounded-xl bg-white p-4 shadow-sm"><b>Gambar unggulan</b>
            @if ($gambar)<img src="{{ Storage::disk('public')->url($gambar) }}" alt="{{ $gambar_alt }}" class="aspect-video w-full rounded object-cover">
                <input wire:model="gambar_alt" placeholder="Teks alt (deskripsi gambar)" class="w-full rounded border px-2 py-1 text-sm">
                <button type="button" wire:click="hapusGambar" class="text-xs text-red-600">Hapus gambar</button>
            @endif
            <label class="block cursor-pointer rounded border-2 border-dashed border-slate-300 p-4 text-center text-sm text-slate-500">Unggah gambar (otomatis jadi WebP, maks. 5 MB)<input type="file" wire:model="gambarBaru" accept="image/*" class="hidden"></label>
            <label class="block cursor-pointer text-center text-xs text-emerald-700">+ Sisipkan gambar ke isi artikel<input type="file" wire:model="gambarSisip" accept="image/*" class="hidden"></label>
            <div wire:loading wire:target="gambarBaru,gambarSisip" class="text-xs text-slate-500">Memproses gambar…</div>
            @error('gambarBaru')<div class="text-xs text-red-600">{{ $message }}</div>@enderror @error('gambarSisip')<div class="text-xs text-red-600">{{ $message }}</div>@enderror</div>
        {{-- Panel SEO: langkah berikutnya --}}
    </aside>
</form>

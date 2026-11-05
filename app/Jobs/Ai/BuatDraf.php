<?php

namespace App\Jobs\Ai;

use App\Models\AiRun;
use App\Models\Artikel;
use App\Models\Tag;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiResponse;
use App\Services\Ai\Pipeline\Json;
use App\Services\Ai\Pipeline\PipelineException;
use App\Services\Ai\Pipeline\Prompt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BuatDraf extends TahapJob
{
    protected function tahap(): string
    {
        return 'draf';
    }

    protected function jalankan(AiRun $run): ?AiResponse
    {
        $tema = $run->tema ?? throw new PipelineException('Tema sudah dihapus.');
        $outline = $run->data['outline'] ?? throw new PipelineException('Outline belum ada.');

        $resp = app(AiClient::class)->chat(Prompt::draf($outline, (string) ($run->data['ringkasan'] ?? '')), 0.6, 120);
        $d = Json::urai($resp->teks);

        $isi = is_string($d['isi_markdown'] ?? null) ? trim($d['isi_markdown']) : '';
        if (mb_strlen($isi) < 300) {
            throw new PipelineException('Isi artikel dari AI terlalu pendek.');
        }

        $faq = collect((array) ($d['faq'] ?? []))
            ->filter(fn ($f) => is_array($f) && is_string($f['q'] ?? null) && is_string($f['a'] ?? null) && trim($f['q']) !== '' && trim($f['a']) !== '')
            ->map(fn ($f) => ['q' => mb_substr(trim($f['q']), 0, 200), 'a' => mb_substr(trim($f['a']), 0, 1000)])
            ->take(5)->values()->all();

        $sumber = collect($run->data['sumber'] ?? [])->map(fn ($s) => ['judul' => $s['judul'], 'url' => $s['url']])->all();

        DB::transaction(function () use ($run, $tema, $outline, $d, $isi, $faq, $sumber): void {
            // Status SELALU draf — tidak ada jalur auto-publish dari pipeline.
            $artikel = Artikel::create([
                'user_id' => $tema->user_id,
                'kategori_id' => $tema->kategori_id,
                'tema_id' => $tema->id,
                'judul' => mb_substr(is_string($d['judul'] ?? null) && trim($d['judul']) !== '' ? trim($d['judul']) : $outline['judul'], 0, 160),
                'ringkasan' => mb_substr(trim((string) ($d['meta_description'] ?? '')), 0, 320) ?: null,
                'kata_kunci' => $outline['kata_kunci_utama'],
                'isi' => $isi,
                'faq' => $faq,
                'sumber' => $sumber,
                'dibuat_ai' => true,
                'status' => Artikel::DRAF,
                'terbit_pada' => null,
            ]);

            $tagIds = collect((array) ($d['tag'] ?? []))
                ->filter(fn ($t) => is_string($t) && trim($t) !== '')
                ->map(fn ($t) => Str::slug(mb_substr(trim($t), 0, 40)))
                ->filter()->unique()->take(4)
                ->map(fn ($slug) => Tag::query()->firstOrCreate(['slug' => $slug], ['nama' => str_replace('-', ' ', $slug)])->id)
                ->all();
            $artikel->tag()->sync($tagIds);

            $run->update(['status' => AiRun::SELESAI, 'artikel_id' => $artikel->id, 'pesan' => null]);
        });

        return $resp;
    }
}

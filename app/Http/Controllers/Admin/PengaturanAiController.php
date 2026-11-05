<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanPengaturanAiRequest;
use App\Services\Ai\AiClient;
use App\Services\Search\SearchManager;
use App\Support\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class PengaturanAiController extends Controller
{
    public function edit(): View
    {
        return view('admin.pengaturan-ai', [
            'baseUrl' => Settings::get('ai_base_url', 'https://api.openai.com/v1'),
            'model' => Settings::get('ai_model', ''),
            'adaAiKey' => Settings::has('ai_api_key'),
            'provider' => Settings::get('search_provider', 'tavily'),
            'adaSearchKey' => Settings::has('search_api_key'),
            'adapter' => array_keys(SearchManager::ADAPTER),
        ]);
    }

    public function update(SimpanPengaturanAiRequest $request): RedirectResponse
    {
        $d = $request->validated();

        Settings::set('ai_base_url', rtrim($d['ai_base_url'], '/'));
        Settings::set('ai_model', $d['ai_model']);
        Settings::set('search_provider', $d['search_provider']);
        // Key hanya diganti bila diisi; nilai lama tidak pernah dikirim kembali ke browser.
        if (filled($d['ai_api_key'] ?? null)) {
            Settings::set('ai_api_key', $d['ai_api_key'], true);
        }
        if (filled($d['search_api_key'] ?? null)) {
            Settings::set('search_api_key', $d['search_api_key'], true);
        }

        return redirect()->route('admin.pengaturan.ai')->with('status', 'Pengaturan AI disimpan.');
    }

    public function ujiAi(AiClient $ai): RedirectResponse
    {
        try {
            return back()->with('status', $ai->uji());
        } catch (Throwable $e) {
            return back()->withErrors(['ai' => 'Uji koneksi model gagal: '.$e->getMessage()]);
        }
    }

    public function ujiPencarian(SearchManager $search): RedirectResponse
    {
        try {
            $hasil = $search->provider()->cari('laravel', 1);

            return back()->with('status', 'Pencarian web terhubung ('.count($hasil).' hasil uji).');
        } catch (Throwable $e) {
            return back()->withErrors(['search' => 'Uji pencarian gagal: '.$e->getMessage()]);
        }
    }
}

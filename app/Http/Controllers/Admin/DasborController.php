<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiLog;
use App\Models\Artikel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DasborController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $dasar = Artikel::query()->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id));

        $hitung = (clone $dasar)->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        $data = [
            'hitung' => [
                'Draf' => (int) ($hitung[Artikel::DRAF] ?? 0),
                'Terjadwal' => (int) ($hitung[Artikel::TERJADWAL] ?? 0),
                'Terbit' => (int) ($hitung[Artikel::TERBIT] ?? 0),
            ],
            'draf' => (clone $dasar)->where('status', Artikel::DRAF)->with('penulis:id,name')->latest('updated_at')->limit(8)->get(),
            'ai' => null,
        ];

        if ($user->isAdmin()) {
            $minggu = AiLog::query()->where('created_at', '>=', now()->subDays(7));
            $data['ai'] = [
                'token' => (int) (clone $minggu)->sum('token_masuk') + (int) (clone $minggu)->sum('token_keluar'),
                'error' => (clone $minggu)->where('status', 'error')->count(),
                'drafAi' => Artikel::query()->where('dibuat_ai', true)->where('status', Artikel::DRAF)->count(),
            ];
        }

        return view('admin.dasbor', $data);
    }
}

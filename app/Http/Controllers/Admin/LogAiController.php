<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiLog;
use App\Models\AiRun;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LogAiController extends Controller
{
    public function __invoke(Request $request): View
    {
        $status = $request->query('status') === 'error' ? 'error' : null;

        $log = AiLog::query()
            ->with('run.tema:id,nama')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('created_at')->latest('id')
            ->paginate(30)->withQueryString();

        $minggu = AiLog::query()->where('created_at', '>=', now()->subDays(7));
        $bulan = AiLog::query()->where('created_at', '>=', now()->subDays(30));

        return view('admin.log-ai', [
            'log' => $log,
            'status' => $status,
            'ringkas' => [
                'token7' => (int) (clone $minggu)->sum('token_masuk') + (int) (clone $minggu)->sum('token_keluar'),
                'token30' => (int) (clone $bulan)->sum('token_masuk') + (int) (clone $bulan)->sum('token_keluar'),
                'error7' => (clone $minggu)->where('status', 'error')->count(),
            ],
            // Proses yang gagal sebelum/ di antara tahap (mis. pengaturan belum lengkap)
            'runGagal' => AiRun::query()->with('tema:id,nama')->where('status', AiRun::GAGAL)->latest()->limit(10)->get(),
        ]);
    }
}

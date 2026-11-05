<?php

namespace App\Jobs\Ai;

use App\Models\AiLog;
use App\Models\AiRun;
use App\Services\Ai\AiResponse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * Dasar tiap tahap pipeline: satu job = satu panggilan eksternal, dengan log token/error.
 */
abstract class TahapJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(public int $runId) {}

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [30];
    }

    abstract protected function tahap(): string;

    /** Kerjakan tahap; kembalikan AiResponse bila memakai AI (untuk pencatatan token). */
    abstract protected function jalankan(AiRun $run): ?AiResponse;

    public function handle(): void
    {
        $run = AiRun::query()->findOrFail($this->runId);
        if ($run->status === AiRun::GAGAL) {
            return;
        }
        $run->update(['status' => AiRun::BERJALAN]);

        $mulai = hrtime(true);
        try {
            $resp = $this->jalankan($run);
        } catch (Throwable $e) {
            $this->catat($run, 'error', null, $mulai, $e->getMessage());
            throw $e;
        }
        $this->catat($run, 'ok', $resp, $mulai);
    }

    public function failed(Throwable $e): void
    {
        AiRun::query()->whereKey($this->runId)->update(['status' => AiRun::GAGAL, 'pesan' => mb_substr("{$this->tahap()}: {$e->getMessage()}", 0, 500), 'updated_at' => now()]);
    }

    private function catat(AiRun $run, string $status, ?AiResponse $resp, int|float $mulai, ?string $pesan = null): void
    {
        AiLog::create([
            'ai_run_id' => $run->id,
            'tahap' => $this->tahap(),
            'status' => $status,
            'token_masuk' => $resp->tokenMasuk ?? 0,
            'token_keluar' => $resp->tokenKeluar ?? 0,
            'durasi_ms' => (int) ((hrtime(true) - $mulai) / 1_000_000),
            'pesan' => $pesan !== null ? mb_substr($pesan, 0, 500) : null,
        ]);
    }
}

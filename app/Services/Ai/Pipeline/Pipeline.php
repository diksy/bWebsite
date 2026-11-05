<?php

namespace App\Services\Ai\Pipeline;

use App\Jobs\Ai\BuatDraf;
use App\Jobs\Ai\BuatOutline;
use App\Jobs\Ai\CariWeb;
use App\Jobs\Ai\RingkasSumber;
use App\Models\AiRun;
use App\Models\Tema;
use App\Services\Ai\AiClient;
use App\Services\Search\SearchManager;
use Illuminate\Support\Facades\Bus;

class Pipeline
{
    public function __construct(private AiClient $ai, private SearchManager $search) {}

    /**
     * Mulai satu eksekusi: rantai job kecil per tahap (aman dari batas waktu shared hosting).
     * Hasil akhir SELALU berstatus Draf.
     *
     * @throws PipelineException bila prasyarat tidak terpenuhi
     */
    public function mulai(Tema $tema): AiRun
    {
        if (! $this->ai->tersedia()) {
            throw new PipelineException('Pengaturan AI belum lengkap (base URL, API key, model).');
        }
        if (! $this->search->provider()->tersedia()) {
            throw new PipelineException('API key pencarian web belum diisi.');
        }
        if ($tema->kuotaTersisa() <= 0) {
            throw PipelineException::wajar("Batas {$tema->batas_per_minggu} draf/minggu untuk tema ini sudah tercapai.");
        }
        $berjalan = AiRun::query()->where('tema_id', $tema->id)->whereIn('status', [AiRun::MENUNGGU, AiRun::BERJALAN])
            ->where('created_at', '>=', now()->subHours(6))->exists();
        if ($berjalan) {
            throw PipelineException::wajar('Tema ini masih punya proses yang berjalan.');
        }

        $kataKunci = $tema->daftarKataKunci();
        $urutan = AiRun::query()->where('tema_id', $tema->id)->count();
        $fokus = $kataKunci !== [] ? $kataKunci[$urutan % count($kataKunci)] : $tema->nama; // putar kata kunci

        $run = AiRun::create(['tema_id' => $tema->id, 'status' => AiRun::MENUNGGU, 'topik' => mb_substr($fokus, 0, 200)]);
        $tema->forceFill(['terakhir_jalan' => now()])->save();

        Bus::chain([
            new CariWeb($run->id),
            new RingkasSumber($run->id),
            new BuatOutline($run->id),
            new BuatDraf($run->id),
        ])->dispatch();

        return $run;
    }

    /** Catat kegagalan prasyarat agar terlihat di Log AI. */
    public function catatGagal(Tema $tema, string $pesan): AiRun
    {
        return AiRun::create(['tema_id' => $tema->id, 'status' => AiRun::GAGAL, 'topik' => mb_substr($tema->nama, 0, 200), 'pesan' => mb_substr($pesan, 0, 500)]);
    }
}

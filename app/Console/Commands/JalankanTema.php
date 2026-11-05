<?php

namespace App\Console\Commands;

use App\Models\Tema;
use App\Services\Ai\Pipeline\Pipeline;
use App\Services\Ai\Pipeline\PipelineException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tema:jalankan')]
#[Description('Mulai pipeline penulis AI untuk tema yang jatuh tempo dan masih punya kuota draf')]
class JalankanTema extends Command
{
    public function handle(Pipeline $pipeline): int
    {
        $mulai = 0;

        Tema::query()->aktif()->get()->filter->layakJalan()->each(function (Tema $tema) use ($pipeline, &$mulai): void {
            try {
                $pipeline->mulai($tema);
                $mulai++;
            } catch (PipelineException $e) {
                if (! $e->wajar) {
                    $pipeline->catatGagal($tema, $e->getMessage());
                }
                $this->warn("Tema '{$tema->nama}' dilewati: {$e->getMessage()}");
            }
        });

        $this->info("{$mulai} pipeline dimulai.");

        return self::SUCCESS;
    }
}

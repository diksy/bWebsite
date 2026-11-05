<?php

namespace App\Services\Ai\Pipeline;

use RuntimeException;

class PipelineException extends RuntimeException
{
    /** Penolakan wajar (kuota penuh / sedang berjalan): tidak perlu dicatat sebagai kegagalan. */
    public bool $wajar = false;

    public static function wajar(string $pesan): self
    {
        $e = new self($pesan);
        $e->wajar = true;

        return $e;
    }
}

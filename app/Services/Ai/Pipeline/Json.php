<?php

namespace App\Services\Ai\Pipeline;

final class Json
{
    /**
     * Ambil objek JSON dari balasan model (toleran terhadap pagar kode ``` dan teks pembuka).
     *
     * @return array<string, mixed>
     */
    public static function urai(string $teks): array
    {
        $awal = strpos($teks, '{');
        $akhir = strrpos($teks, '}');
        if ($awal === false || $akhir === false || $akhir <= $awal) {
            throw new PipelineException('Balasan AI bukan JSON.');
        }

        $data = json_decode(substr($teks, $awal, $akhir - $awal + 1), true);
        if (! is_array($data)) {
            throw new PipelineException('JSON dari AI tidak valid.');
        }

        return $data;
    }
}

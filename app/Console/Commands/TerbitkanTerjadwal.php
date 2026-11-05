<?php

namespace App\Console\Commands;

use App\Models\Artikel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('artikel:terbitkan-terjadwal')]
#[Description('Terbitkan artikel terjadwal yang waktunya sudah tiba')]
class TerbitkanTerjadwal extends Command
{
    public function handle(): int
    {
        $jumlah = 0;

        Artikel::query()
            ->where('status', Artikel::TERJADWAL)
            ->where('terbit_pada', '<=', now())
            ->orderBy('terbit_pada')
            ->each(function (Artikel $a) use (&$jumlah): void {
                $a->update(['status' => Artikel::TERBIT]); // hook model mengirim IndexNow
                $jumlah++;
            });

        $this->info("{$jumlah} artikel diterbitkan.");

        return self::SUCCESS;
    }
}

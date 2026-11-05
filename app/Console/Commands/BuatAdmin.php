<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:buat-admin {email} {nama} {--password= : Kosongkan untuk dibuat acak}')]
#[Description('Buat atau perbarui akun admin pertama')]
class BuatAdmin extends Command
{
    public function handle(): int
    {
        $password = $this->option('password') ?: str()->random(14);

        User::query()->updateOrCreate(
            ['email' => $this->argument('email')],
            ['name' => $this->argument('nama'), 'peran' => User::PERAN_ADMIN, 'is_active' => true, 'password' => $password],
        );

        $this->info('Admin siap: '.$this->argument('email'));
        if (! $this->option('password')) {
            $this->line('Kata sandi sementara: '.$password);
        }

        return self::SUCCESS;
    }
}

<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Data awal minimal; aman dijalankan berulang. */
    public function run(): void
    {
        foreach (['Teknologi', 'Bisnis', 'Gaya Hidup'] as $nama) {
            Kategori::query()->firstOrCreate(['slug' => str($nama)->slug()->toString()], ['nama' => $nama]);
        }

        // Penulis bawaan untuk konten AI: tidak punya kata sandi sehingga tidak bisa login.
        User::query()->firstOrCreate(
            ['email' => 'redaksi@invalid.local'],
            ['name' => 'Redaksi', 'peran' => User::PERAN_PENULIS, 'is_active' => true, 'bio' => 'Tim redaksi.'],
        );
    }
}

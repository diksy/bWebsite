<?php

namespace Database\Factories;

use App\Models\Tema;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Tema> */
class TemaFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'nama' => fake()->sentence(3),
            'kata_kunci' => 'seo umkm, blog bisnis',
            'frekuensi' => 'harian',
            'user_id' => User::factory(),
            'batas_per_minggu' => 3,
            'aktif' => true,
        ];
    }
}

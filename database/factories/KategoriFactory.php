<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Kategori> */
class KategoriFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['nama' => fake()->unique()->words(2, true), 'deskripsi' => fake()->sentence()];
    }
}

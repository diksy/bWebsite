<?php

namespace Database\Factories;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Artikel> */
class ArtikelFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'judul' => fake()->unique()->sentence(5),
            'ringkasan' => fake()->sentence(18),
            'isi' => "Paragraf pembuka.\n\n## Subjudul\n\n".fake()->paragraph(),
            'status' => Artikel::DRAF,
        ];
    }

    public function terbit(): static
    {
        return $this->state(['status' => Artikel::TERBIT, 'terbit_pada' => now()->subHour()]);
    }
}

<?php

namespace Database\Factories;

use App\Models\GayaKustom;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GayaKustom>
 */
class GayaKustomFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $slug = Str::slug(fake()->unique()->words(2, true));

        return ['nama' => Str::title(str_replace('-', ' ', $slug)), 'slug' => $slug, 'berkas' => "gaya/{$slug}.css", 'ukuran' => 400];
    }
}

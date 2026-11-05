<?php

namespace App\Models;

use Database\Factories\KategoriFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['nama', 'slug', 'deskripsi'])]
class Kategori extends Model
{
    /** @use HasFactory<KategoriFactory> */
    use HasFactory;

    protected $table = 'kategori';

    protected static function booted(): void
    {
        static::saving(function (Kategori $k): void {
            $k->slug = Str::slug($k->slug ?: $k->nama);
        });
    }
}

<?php

namespace App\Models;

use Database\Factories\GayaKustomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['nama', 'slug', 'berkas', 'ukuran'])]
class GayaKustom extends Model
{
    /** @use HasFactory<GayaKustomFactory> */
    use HasFactory;

    protected $table = 'gaya_kustom';

    protected static function booted(): void
    {
        static::deleted(fn (GayaKustom $g) => Storage::disk('public')->delete($g->berkas));
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->berkas).'?v='.$this->updated_at->timestamp;
    }
}

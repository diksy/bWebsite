<?php

namespace App\Models;

use Database\Factories\TemaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'kata_kunci', 'frekuensi', 'kategori_id', 'user_id', 'batas_per_minggu', 'aktif', 'terakhir_jalan'])]
class Tema extends Model
{
    /** @use HasFactory<TemaFactory> */
    use HasFactory;

    /** Jeda minimal antar eksekusi (hari) per frekuensi. */
    public const FREKUENSI = ['harian' => 1, 'mingguan' => 7, 'dua_mingguan' => 14, 'bulanan' => 30];

    public const LABEL = ['harian' => 'Harian', 'mingguan' => 'Mingguan', 'dua_mingguan' => 'Dua mingguan', 'bulanan' => 'Bulanan'];

    protected $table = 'tema';

    /** @var array<string, string> */
    protected $casts = [
        'aktif' => 'boolean',
        'terakhir_jalan' => 'datetime',
        'batas_per_minggu' => 'integer',
    ];

    /** @return BelongsTo<Kategori, $this> */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    /** @return BelongsTo<User, $this> */
    public function penulis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<Artikel, $this> */
    public function artikel(): HasMany
    {
        return $this->hasMany(Artikel::class);
    }

    /** @param  Builder<Tema>  $query */
    public function scopeAktif(Builder $query): void
    {
        $query->where('aktif', true);
    }

    /** Sudah waktunya dijalankan menurut frekuensi? */
    public function jatuhTempo(): bool
    {
        if (! $this->aktif) {
            return false;
        }
        if ($this->terakhir_jalan === null) {
            return true;
        }

        return $this->terakhir_jalan->copy()->addDays(self::FREKUENSI[$this->frekuensi] ?? 7)->lte(now());
    }

    /** Draf AI yang sudah dibuat minggu ini (Senin–Minggu) untuk tema ini. */
    public function draftMingguIni(): int
    {
        return $this->artikel()->where('dibuat_ai', true)->where('created_at', '>=', now()->startOfWeek())->count();
    }

    public function kuotaTersisa(): int
    {
        return max(0, $this->batas_per_minggu - $this->draftMingguIni());
    }

    /** Layak dijalankan: aktif, jatuh tempo, dan kuota mingguan masih ada. */
    public function layakJalan(): bool
    {
        return $this->jatuhTempo() && $this->kuotaTersisa() > 0;
    }

    /** @return list<string> */
    public function daftarKataKunci(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[,\n]+/', $this->kata_kunci) ?: [])));
    }
}

<?php

namespace App\Models;

use App\Jobs\KirimIndexNow;
use App\Support\Markdown;
use App\Support\Settings;
use Database\Factories\ArtikelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Artikel extends Model
{
    public const DRAF = 'draf';

    public const TERJADWAL = 'terjadwal';

    public const TERBIT = 'terbit';

    /** @use HasFactory<ArtikelFactory> */
    use HasFactory;

    protected $table = 'artikel';

    protected $guarded = [];

    /** @var array<string, string> */
    protected $casts = [
        'terbit_pada' => 'datetime',
        'faq' => 'array',
        'sumber' => 'array',
        'dibuat_ai' => 'boolean',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => self::DRAF, 'dibuat_ai' => false];

    protected static function booted(): void
    {
        static::saving(function (Artikel $a): void {
            if (blank($a->slug)) {
                $a->slug = static::slugUnik($a->judul, $a->id);
            }
            $a->isi_html = Markdown::render((string) $a->isi);
        });

        // Baru terbit (dibuat langsung terbit, atau status berubah menjadi terbit) → ping IndexNow via antrean.
        static::created(function (Artikel $a): void {
            if ($a->status === self::TERBIT) {
                KirimIndexNow::dispatch($a->id)->afterCommit();
            }
        });
        static::updated(function (Artikel $a): void {
            if ($a->status === self::TERBIT && $a->wasChanged('status')) {
                KirimIndexNow::dispatch($a->id)->afterCommit();
            }
        });
    }

    public static function slugUnik(string $judul, ?int $kecualiId = null): string
    {
        $dasar = Str::slug(Str::limit($judul, 80, '')) ?: 'artikel';
        $slug = $dasar;
        $i = 2;
        while (static::query()->where('slug', $slug)->when($kecualiId, fn ($q) => $q->where('id', '!=', $kecualiId))->exists()) {
            $slug = $dasar.'-'.$i++;
        }

        return $slug;
    }

    /** @return BelongsTo<User, $this> */
    public function penulis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<Kategori, $this> */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tag(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'artikel_tag');
    }

    /** @param  Builder<Artikel>  $query */
    public function scopeDiterbitkan(Builder $query): void
    {
        $query->where('status', self::TERBIT)->where('terbit_pada', '<=', now());
    }

    /** URL publik: /{path-artikel}/{slug}, atau /{slug} bila path dikosongkan. */
    public function url(): string
    {
        $path = Settings::get('artikel_path', 'artikel');

        return url(($path ? $path.'/' : '').$this->slug);
    }

    /** FAQ yang lengkap (pertanyaan & jawaban terisi), siap ditampilkan. @return array<int, array{q: string, a: string}> */
    public function faqLengkap(): array
    {
        $out = [];
        foreach ((array) $this->faq as $f) {
            if (is_array($f) && filled($f['q'] ?? null) && filled($f['a'] ?? null)) {
                $out[] = ['q' => (string) $f['q'], 'a' => (string) $f['a']];
            }
        }

        return $out;
    }
}

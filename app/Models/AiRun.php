<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiRun extends Model
{
    public const MENUNGGU = 'menunggu';

    public const BERJALAN = 'berjalan';

    public const SELESAI = 'selesai';

    public const GAGAL = 'gagal';

    protected $table = 'ai_run';

    protected $fillable = ['tema_id', 'artikel_id', 'status', 'topik', 'data', 'pesan', 'created_at', 'updated_at'];

    /** @var array<string, string> */
    protected $casts = ['data' => 'array'];

    /** @return BelongsTo<Tema, $this> */
    public function tema(): BelongsTo
    {
        return $this->belongsTo(Tema::class);
    }

    /** @return BelongsTo<Artikel, $this> */
    public function artikel(): BelongsTo
    {
        return $this->belongsTo(Artikel::class);
    }

    /** @return HasMany<AiLog, $this> */
    public function log(): HasMany
    {
        return $this->hasMany(AiLog::class, 'ai_run_id');
    }

    /** Simpan keadaan antar tahap (digabung ke data yang ada). */
    public function simpanData(string $kunci, mixed $nilai): void
    {
        $data = $this->data ?? [];
        $data[$kunci] = $nilai;
        $this->update(['data' => $data]);
    }
}

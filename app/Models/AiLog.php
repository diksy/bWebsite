<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'ai_log';

    protected $guarded = [];

    /** @var array<string, string> */
    protected $casts = ['token_masuk' => 'integer', 'token_keluar' => 'integer', 'durasi_ms' => 'integer'];

    /** @return BelongsTo<AiRun, $this> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(AiRun::class, 'ai_run_id');
    }
}

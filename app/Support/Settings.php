<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Pengaturan situs (key-value di tabel settings). Nilai sensitif disimpan terenkripsi.
 */
final class Settings
{
    /** @var array<string, array{value: ?string, encrypted: bool}>|null */
    private static ?array $cache = null;

    /** @return array<string, array{value: ?string, encrypted: bool}> */
    private static function load(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        try {
            $rows = DB::table('settings')->get(['key', 'value', 'encrypted']);
        } catch (Throwable) {
            return []; // tabel belum ada (mis. sebelum migrate); jangan di-cache
        }

        $out = [];
        foreach ($rows as $row) {
            $out[$row->key] = ['value' => $row->value, 'encrypted' => (bool) $row->encrypted];
        }

        return self::$cache = $out;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $row = self::load()[$key] ?? null;
        if ($row === null || $row['value'] === null || $row['value'] === '') {
            return $default;
        }

        if ($row['encrypted']) {
            try {
                return Crypt::decryptString($row['value']);
            } catch (Throwable) {
                return $default;
            }
        }

        return $row['value'];
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    public static function set(string $key, ?string $value, bool $encrypted = false): void
    {
        $stored = ($encrypted && $value !== null && $value !== '') ? Crypt::encryptString($value) : $value;

        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $stored, 'encrypted' => $encrypted, 'updated_at' => now(), 'created_at' => now()],
        );

        self::flush();
    }

    public static function flush(): void
    {
        self::$cache = null;
    }
}

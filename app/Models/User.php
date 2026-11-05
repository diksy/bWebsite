<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'bio', 'email', 'peran', 'is_active', 'password', 'sso_subject'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const PERAN_ADMIN = 'admin';

    public const PERAN_PENULIS = 'penulis';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if (blank($user->slug)) {
                $user->slug = static::slugUnik($user->name, $user->id);
            }
        });
    }

    public static function slugUnik(string $nama, ?int $kecualiId = null): string
    {
        $dasar = Str::slug($nama) ?: 'penulis';
        $slug = $dasar;
        $i = 2;
        while (static::query()->where('slug', $slug)->when($kecualiId, fn ($q) => $q->where('id', '!=', $kecualiId))->exists()) {
            $slug = $dasar.'-'.$i++;
        }

        return $slug;
    }

    public function isAdmin(): bool
    {
        return $this->peran === self::PERAN_ADMIN;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}

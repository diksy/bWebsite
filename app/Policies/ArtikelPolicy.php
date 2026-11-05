<?php

namespace App\Policies;

use App\Models\Artikel;
use App\Models\User;

class ArtikelPolicy
{
    /** Admin mengelola semua artikel; penulis hanya miliknya. */
    public function kelola(User $user, Artikel $artikel): bool
    {
        return $user->is_active && ($user->isAdmin() || $artikel->user_id === $user->id);
    }
}

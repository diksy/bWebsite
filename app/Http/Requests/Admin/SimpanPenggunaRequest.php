<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanPenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        /** @var User|null $target */
        $target = $this->route('pengguna');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($target?->id)],
            'peran' => ['required', Rule::in([User::PERAN_ADMIN, User::PERAN_PENULIS])],
            'bio' => ['nullable', 'string', 'max:600'],
            'password' => [$target ? 'nullable' : 'nullable', 'string', 'min:8', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}

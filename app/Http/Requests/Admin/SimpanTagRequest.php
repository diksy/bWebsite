<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SimpanTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:60'],
            'slug' => ['required', 'string', 'max:80', 'unique:tag,slug'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug((string) $this->input('nama'))]);
    }
}

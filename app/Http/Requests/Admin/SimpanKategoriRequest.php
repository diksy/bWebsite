<?php

namespace App\Http\Requests\Admin;

use App\Models\Kategori;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SimpanKategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        /** @var Kategori|null $k */
        $k = $this->route('kategori');

        return [
            'nama' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', Rule::unique('kategori', 'slug')->ignore($k?->id)],
            'deskripsi' => ['nullable', 'string', 'max:300'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug((string) ($this->input('slug') ?: $this->input('nama')))]);
    }
}

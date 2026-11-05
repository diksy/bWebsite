<?php

namespace App\Http\Requests\Admin;

use App\Models\Tema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanTemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:160'],
            'kata_kunci' => ['required', 'string', 'max:500'],
            'frekuensi' => ['required', Rule::in(array_keys(Tema::FREKUENSI))],
            'kategori_id' => ['nullable', 'integer', 'exists:kategori,id'],
            'user_id' => ['required', Rule::exists('users', 'id')->where('is_active', true)],
            'batas_per_minggu' => ['required', 'integer', 'min:1', 'max:20'],
            'aktif' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['aktif' => $this->boolean('aktif')]);
    }
}

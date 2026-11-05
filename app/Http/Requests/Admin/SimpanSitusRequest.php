<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanSitusRequest extends FormRequest
{
    /** Path yang dipakai fitur lain; tidak boleh dipakai sebagai path admin/artikel. */
    public const DICADANGKAN = [
        'cari', 'kategori', 'tag', 'penulis', 'feed', 'rss', 'sitemap', 'sitemap.xml', 'robots.txt', 'storage',
        'build', 'up', 'sso', 'livewire', 'api', 'storage', 'indexnow', 'favicon.ico',
    ];

    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $slugPath = ['regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', 'max:40', Rule::notIn(self::DICADANGKAN)];

        return [
            'nama_situs' => ['required', 'string', 'max:60'],
            'tagline' => ['nullable', 'string', 'max:140'],
            'admin_path' => ['required', ...$slugPath],
            'artikel_path' => ['nullable', ...$slugPath, 'different:admin_path'],
            'sso_aktif' => ['boolean'],
            'sso_wajib' => ['boolean'],
            'sso_base_url' => ['nullable', 'url:https', 'max:190'],
            'sso_client_id' => ['nullable', 'string', 'max:120'],
            'sso_client_secret' => ['nullable', 'string', 'max:300'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'admin_path.regex' => 'Path admin hanya huruf kecil, angka, dan tanda hubung.',
            'artikel_path.regex' => 'Path artikel hanya huruf kecil, angka, dan tanda hubung.',
            'admin_path.not_in' => 'Path admin memakai nama yang dicadangkan sistem.',
            'artikel_path.not_in' => 'Path artikel memakai nama yang dicadangkan sistem.',
            'artikel_path.different' => 'Path artikel tidak boleh sama dengan path admin.',
            'sso_base_url.url' => 'Base URL SSO harus berupa URL https.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'admin_path' => strtolower(trim((string) $this->input('admin_path'), '/ ')),
            'artikel_path' => strtolower(trim((string) $this->input('artikel_path'), '/ ')) ?: null,
            'sso_aktif' => $this->boolean('sso_aktif'),
            'sso_wajib' => $this->boolean('sso_wajib'),
        ]);
    }

    /** @return array<int, \Closure> */
    public function after(): array
    {
        return [function ($validator): void {
            if ($this->boolean('sso_wajib') && ! $this->boolean('sso_aktif')) {
                $validator->errors()->add('sso_wajib', 'SSO wajib hanya bisa diaktifkan bila SSO aktif.');
            }
            if ($this->boolean('sso_aktif') && (blank($this->input('sso_base_url')) || blank($this->input('sso_client_id')))) {
                $validator->errors()->add('sso_aktif', 'Base URL dan Client ID wajib diisi untuk mengaktifkan SSO.');
            }
        }];
    }
}

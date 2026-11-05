<?php

namespace App\Http\Requests\Admin;

use App\Services\Gaya\Gaya;
use App\Services\Gaya\SanitasiCss;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class UnggahGayaRequest extends FormRequest
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
            'slug' => ['required', 'string', 'max:80', 'unique:gaya_kustom,slug', fn ($a, $v, $fail) => Gaya::bawaan((string) $v) ? $fail('Nama ini bentrok dengan gaya bawaan.') : null],
            'berkas' => ['required', 'file', 'extensions:css', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug((string) $this->input('nama'))]);
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $v): void {
            $berkas = $this->file('berkas');
            if ($v->errors()->has('berkas') || ! $berkas instanceof UploadedFile) {
                return;
            }
            foreach ((new SanitasiCss)->periksa((string) file_get_contents($berkas->getRealPath())) as $alasan) {
                $v->errors()->add('berkas', $alasan);
            }
        }];
    }
}

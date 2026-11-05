<?php

namespace App\Http\Requests\Admin;

use App\Services\Search\SearchManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanPengaturanAiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'ai_base_url' => ['required', 'url:https,http', 'max:190'],
            'ai_api_key' => ['nullable', 'string', 'max:300'],
            'ai_model' => ['required', 'string', 'max:120'],
            'search_provider' => ['required', Rule::in(array_keys(SearchManager::ADAPTER))],
            'search_api_key' => ['nullable', 'string', 'max:300'],
        ];
    }
}

<?php

namespace App\Http\Requests\Growth;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeoEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'default_language' => ['nullable', 'string', 'max:12'],
            'knowledge_graph_id' => ['nullable', 'string', 'max:255'],
            'social_profiles' => ['nullable', 'array'],
            'social_profiles.*' => ['nullable', 'url', 'max:255'],
        ];
    }
}

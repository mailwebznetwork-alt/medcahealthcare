<?php

namespace App\Http\Requests\Growth;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeoTechnicalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'robots_enabled' => ['required', 'boolean'],
            'sitemap_enabled' => ['required', 'boolean'],
            'canonical_mode' => ['required', 'in:self,domain'],
            'robots_content' => ['nullable', 'string'],
            'sitemap_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}

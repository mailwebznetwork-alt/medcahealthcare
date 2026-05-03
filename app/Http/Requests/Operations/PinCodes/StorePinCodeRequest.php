<?php

namespace App\Http\Requests\Operations\PinCodes;

use App\Models\PinCode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePinCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', PinCode::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pincode' => ['required', 'string', 'regex:/^\d{6,10}$/', 'unique:'.PinCode::class.',pincode'],
            'area_name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'locality' => ['nullable', 'string', 'max:255'],
            'is_serviceable' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'delivery_charge' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:2000'],
            'seo_keywords' => ['nullable', 'string', 'max:2000'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique(PinCode::class, 'slug')],
            'geo_page_ready' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug');
        if (is_string($slug) && trim($slug) === '') {
            $this->merge(['slug' => null]);
        }

        $this->merge([
            'is_serviceable' => $this->boolean('is_serviceable', true),
            'is_active' => $this->boolean('is_active', true),
            'geo_page_ready' => $this->boolean('geo_page_ready', false),
        ]);
    }
}

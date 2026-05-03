<?php

namespace App\Http\Requests;

use App\ModuleAccess;
use Illuminate\Foundation\Http\FormRequest;

class UpdateModuleAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $rules = [
            'module_access' => ['required', 'array'],
        ];

        foreach (ModuleAccess::keys() as $key) {
            $rules['module_access.'.$key] = ['required', 'boolean'];
        }

        return $rules;
    }

    /**
     * Unchecked boxes are absent — normalize every module key to a boolean.
     */
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (ModuleAccess::keys() as $key) {
            $normalized[$key] = $this->boolean('module_access.'.$key);
        }

        $this->merge([
            'module_access' => $normalized,
        ]);
    }
}

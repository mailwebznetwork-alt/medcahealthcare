<?php

namespace App\Http\Requests\SiteArchitect;

use App\Models\FieldDefinition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Module::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:80', 'alpha_dash', 'unique:modules,slug'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.label' => ['required', 'string', 'max:120'],
            'fields.*.field_name' => ['required', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_]*$/', 'distinct'],
            'fields.*.field_type' => ['required', Rule::in(FieldDefinition::types())],
            'fields.*.is_required' => ['nullable', 'boolean'],
            'fields.*.options' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'fields.*.label' => 'field label',
            'fields.*.field_name' => 'field name',
            'fields.*.field_type' => 'field type',
        ];
    }
}

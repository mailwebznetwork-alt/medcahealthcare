<?php

namespace App\Http\Requests\SiteArchitect;

use App\Models\FieldDefinition;
use App\Models\Module;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $module = $this->route('module');

        return $module instanceof Module
            && ($this->user()?->can('update', $module) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Module $module */
        $module = $this->route('module');

        return [
            'name' => ['required', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.id' => ['nullable', 'integer', Rule::exists('field_definitions', 'id')->where('module_id', $module->id)],
            'fields.*.label' => ['required', 'string', 'max:120'],
            'fields.*.field_name' => ['required', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_]*$/'],
            'fields.*.field_type' => ['required', Rule::in(FieldDefinition::types())],
            'fields.*.is_required' => ['nullable', 'boolean'],
            'fields.*.options' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

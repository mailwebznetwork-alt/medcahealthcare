<?php

namespace App\Services\DynamicModules;

use App\Models\FieldDefinition;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class DynamicRecordService
{
    public function __construct(
        private readonly DynamicFieldValidator $validator,
        private readonly DynamicRecordRepository $repository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function validateAndExtract(Request $request, Module $module): array
    {
        $module->loadMissing('fieldDefinitions');

        if ($module->fieldDefinitions->isEmpty()) {
            throw ValidationException::withMessages([
                'fields' => [__('Define at least one field for this module before saving records.')],
            ]);
        }

        $validated = $request->validate(
            $this->validator->rulesFor($module, 'fields'),
            [],
            $this->validator->attributesFor($module, 'fields')
        );

        return $this->normalizeFieldValues($module, $validated['fields'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function normalizeFieldValues(Module $module, array $values): array
    {
        $allowed = $module->fieldDefinitions->pluck('field_name')->all();
        $payload = Arr::only($values, $allowed);

        foreach ($module->fieldDefinitions as $field) {
            if ($field->field_type !== FieldDefinition::TYPE_BOOLEAN) {
                continue;
            }

            $payload[$field->field_name] = filter_var(
                $payload[$field->field_name] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function create(Module $module, array $values): int
    {
        return $this->repository->create($module, $values);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function update(Module $module, int $recordId, array $values): void
    {
        $this->repository->update($module, $recordId, $values);
    }
}

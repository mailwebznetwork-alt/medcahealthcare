<?php

namespace App\Services\DynamicModules;

use App\Models\FieldDefinition;
use App\Models\Module;
use Illuminate\Support\Facades\DB;

class LegacyModuleSchemaService
{
    public function __construct(
        private readonly DynamicTableManager $tableManager,
    ) {}

    /**
     * @param  array{name?: string, fields: list<array<string, mixed>>}  $data
     */
    public function sync(Module $module, array $data): void
    {
        abort_unless($module->isLegacy(), 404);

        DB::transaction(function () use ($module, $data): void {
            if (isset($data['name'])) {
                $module->update(['name' => $data['name']]);
            }

            $existingIds = collect($data['fields'])
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all();

            $removedColumnNames = $module->fieldDefinitions()
                ->whereNotIn('id', $existingIds)
                ->pluck('field_name')
                ->all();

            $module->fieldDefinitions()
                ->whereNotIn('id', $existingIds)
                ->delete();

            foreach (array_values($data['fields']) as $index => $fieldInput) {
                $attributes = [
                    'label' => $fieldInput['label'],
                    'field_name' => $fieldInput['field_name'],
                    'field_type' => $fieldInput['field_type'],
                    'is_required' => (bool) ($fieldInput['is_required'] ?? false),
                    'sort_order' => $index,
                    'settings' => $this->settingsFromInput($fieldInput),
                ];

                if (filled($fieldInput['id'] ?? null)) {
                    FieldDefinition::query()
                        ->where('module_id', $module->id)
                        ->whereKey($fieldInput['id'])
                        ->firstOrFail()
                        ->update($attributes);
                } else {
                    $module->fieldDefinitions()->create($attributes);
                }
            }

            $module->load('fieldDefinitions');

            if ($module->usesColumnStorage()) {
                $this->tableManager->syncFields($module, $removedColumnNames);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $fieldInput
     * @return array<string, mixed>
     */
    private function settingsFromInput(array $fieldInput): array
    {
        if (($fieldInput['field_type'] ?? '') !== FieldDefinition::TYPE_SELECT) {
            return [];
        }

        $raw = (string) ($fieldInput['options'] ?? '');
        $options = array_values(array_filter(array_map(
            static fn (string $part): string => trim($part),
            preg_split('/\r\n|\r|\n|,/', $raw) ?: []
        ), static fn (string $option): bool => $option !== ''));

        return ['options' => $options];
    }
}

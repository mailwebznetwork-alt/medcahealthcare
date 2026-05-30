<?php

namespace App\Services\DynamicModules;

use App\Models\FieldDefinition;
use App\Models\Module;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use stdClass;

class DynamicRecordRepository
{
    public function paginate(Module $module, int $perPage = 20): LengthAwarePaginator
    {
        $this->assertTableReady($module);

        return DB::table($module->table_name)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function find(Module $module, int $id): ?stdClass
    {
        $this->assertTableReady($module);

        $record = DB::table($module->table_name)->where('id', $id)->first();

        return $record instanceof stdClass ? $record : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Module $module, array $data): int
    {
        $this->assertTableReady($module);

        $payload = $this->preparePayload($module, $data);

        return (int) DB::table($module->table_name)->insertGetId(array_merge($payload, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Module $module, int $id, array $data): void
    {
        $this->assertTableReady($module);

        $payload = $this->preparePayload($module, $data);
        $payload['updated_at'] = now();

        DB::table($module->table_name)->where('id', $id)->update($payload);
    }

    public function delete(Module $module, int $id): void
    {
        $this->assertTableReady($module);

        DB::table($module->table_name)->where('id', $id)->delete();
    }

    /**
     * @return Collection<int, stdClass>
     */
    public function listColumnsForIndex(Module $module): Collection
    {
        return $module->fieldDefinitions->take(4);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function preparePayload(Module $module, array $data): array
    {
        $module->loadMissing('fieldDefinitions');

        $columns = $module->fieldDefinitions->pluck('field_name')->all();
        $payload = Arr::only($data, $columns);

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

    private function assertTableReady(Module $module): void
    {
        if (! $module->is_active) {
            throw new RuntimeException("Module [{$module->slug}] is inactive.");
        }

        if (! DB::getSchemaBuilder()->hasTable($module->table_name)) {
            throw new RuntimeException("Module table [{$module->table_name}] is missing.");
        }
    }
}

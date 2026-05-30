<?php

namespace App\Services\DynamicModules;

use App\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DynamicModuleInsertCatalog
{
    /**
     * @return list<array{key: string, label: string, source: string}>
     */
    public function forDropdown(): array
    {
        $options = [];

        foreach (array_keys(config('modules', [])) as $key) {
            $options[] = [
                'key' => (string) $key,
                'label' => (string) $key,
                'source' => 'config',
            ];
        }

        if (! Schema::hasTable('modules')) {
            return $options;
        }

        Module::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['slug', 'name'])
            ->each(function (Module $module) use (&$options): void {
                $options[] = [
                    'key' => $module->slug,
                    'label' => $module->name,
                    'source' => 'dynamic',
                ];
            });

        return $options;
    }

    public function isValidKey(string $key): bool
    {
        $key = trim($key);

        if ($key === '') {
            return false;
        }

        if (array_key_exists($key, config('modules', []))) {
            return true;
        }

        if (! Schema::hasTable('modules')) {
            return false;
        }

        return Module::query()
            ->where('slug', $key)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * @return Collection<int, object>
     */
    public function recordsForPublic(Module $module, int $limit = 100): Collection
    {
        if (! Schema::hasTable($module->table_name)) {
            return collect();
        }

        return \Illuminate\Support\Facades\DB::table($module->table_name)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}

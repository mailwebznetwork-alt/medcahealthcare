<?php

namespace App\Services\DynamicModules;

use App\Models\Module;
use Illuminate\Support\Facades\Schema;

class LegacyManagedModuleRegistry
{
    public const string SERVICES = 'services';

    public const string PIN_CODES = 'pin-codes';

    public const string JOB_PORTAL = 'job-portal';

    /**
     * @return list<array{name: string, slug: string, table_name: string, settings: array<string, mixed>}>
     */
    public function definitions(): array
    {
        return [
            [
                'name' => 'Services',
                'slug' => self::SERVICES,
                'table_name' => 'services',
                'settings' => [
                    'legacy' => true,
                    'storage' => 'json',
                    'model' => \App\Models\Service::class,
                ],
            ],
            [
                'name' => 'Pin Codes',
                'slug' => self::PIN_CODES,
                'table_name' => 'pin_codes',
                'settings' => [
                    'legacy' => true,
                    'storage' => 'json',
                    'model' => \App\Models\PinCode::class,
                ],
            ],
            [
                'name' => 'Job Portal',
                'slug' => self::JOB_PORTAL,
                'table_name' => 'vacancies',
                'settings' => [
                    'legacy' => true,
                    'storage' => 'json',
                    'model' => \App\Models\Vacancy::class,
                ],
            ],
        ];
    }

    public function registerDefaults(): void
    {
        if (! Schema::hasTable('modules')) {
            return;
        }

        foreach ($this->definitions() as $definition) {
            Module::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'table_name' => $definition['table_name'],
                    'settings' => $definition['settings'],
                    'is_active' => true,
                ]
            );
        }
    }

    public function find(string $slug): ?Module
    {
        if (! Schema::hasTable('modules')) {
            return null;
        }

        return Module::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function findOrRegister(string $slug): ?Module
    {
        $module = $this->find($slug);

        if ($module !== null) {
            return $module->load('fieldDefinitions');
        }

        $this->registerDefaults();

        return $this->find($slug)?->load('fieldDefinitions');
    }
}

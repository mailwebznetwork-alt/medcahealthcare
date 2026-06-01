<?php

namespace Database\Seeders;

use App\Models\GlobalContentVariable;
use App\Services\Deployment\GlobalContentVariableRepository;
use Illuminate\Database\Seeder;

class GlobalContentVariableSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('global_content_variables.keys', []) as $key => $meta) {
            GlobalContentVariable::query()->firstOrCreate(
                ['key' => $key],
                [
                    'label' => (string) ($meta['label'] ?? $key),
                    'value' => null,
                ]
            );
        }

        GlobalContentVariableRepository::forgetCache();
    }
}

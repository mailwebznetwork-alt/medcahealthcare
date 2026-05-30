<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Services\DynamicModules\LegacyManagedModuleRegistry;
use Illuminate\Database\Seeder;

class LegacyManagedModuleSeeder extends Seeder
{
    public function run(): void
    {
        app(LegacyManagedModuleRegistry::class)->registerDefaults();
    }
}

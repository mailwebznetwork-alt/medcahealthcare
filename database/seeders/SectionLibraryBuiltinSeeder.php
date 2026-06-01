<?php

namespace Database\Seeders;

use App\Models\SectionLibraryItem;
use Illuminate\Database\Seeder;

class SectionLibraryBuiltinSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('section_library_builtin', []) as $slug => $definition) {
            SectionLibraryItem::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => (string) ($definition['name'] ?? $slug),
                    'description' => $definition['description'] ?? null,
                    'blocks_json' => is_array($definition['blocks_json'] ?? null) ? $definition['blocks_json'] : [],
                    'style_pack_slug' => $definition['style_pack_slug'] ?? null,
                    'is_builtin' => true,
                ]
            );
        }
    }
}

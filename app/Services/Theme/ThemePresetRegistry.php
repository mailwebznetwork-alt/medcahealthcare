<?php

namespace App\Services\Theme;

use App\Models\ThemePreset;
use Illuminate\Support\Collection;

class ThemePresetRegistry
{
    /**
     * @return Collection<int, ThemePreset>
     */
    public function publicPresets(): Collection
    {
        return ThemePreset::query()
            ->where('shell', 'public')
            ->orderByDesc('is_builtin')
            ->orderBy('name')
            ->get();
    }

    public function findBySlug(string $slug): ?ThemePreset
    {
        return ThemePreset::query()->where('slug', $slug)->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function builtinDefinition(string $slug): array
    {
        $definitions = config('theme_presets', []);

        return is_array($definitions[$slug] ?? null) ? $definitions[$slug] : [];
    }

    /**
     * @return list<string>
     */
    public function builtinSlugs(): array
    {
        return array_keys(config('theme_presets', []));
    }
}

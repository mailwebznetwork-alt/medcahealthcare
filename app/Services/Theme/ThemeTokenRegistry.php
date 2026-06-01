<?php

namespace App\Services\Theme;

class ThemeTokenRegistry
{
    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    public function groups(): array
    {
        return config('theme_tokens', []);
    }

    /**
     * @return array<string, mixed>
     */
    public function defaultsForGroup(string $group): array
    {
        $items = $this->groups()[$group] ?? [];
        $defaults = [];
        foreach ($items as $key => $meta) {
            if (is_array($meta) && isset($meta['default'])) {
                $defaults[$key] = $meta['default'];
            }
        }

        return $defaults;
    }

    /**
     * @return array<string, string>
     */
    public function cssMapForGroup(string $group): array
    {
        $items = $this->groups()[$group] ?? [];
        $map = [];
        foreach ($items as $key => $meta) {
            if (is_array($meta) && isset($meta['css'])) {
                $map[$key] = (string) $meta['css'];
            }
        }

        return $map;
    }

    /**
     * Flat defaults for all shape groups (radius, shadow, spacing, layout, carousel).
     *
     * @return array<string, string>
     */
    public function defaultShapeTokens(): array
    {
        $merged = [];
        foreach (['radius', 'shadow', 'spacing', 'layout', 'carousel'] as $group) {
            $merged = array_merge($merged, $this->defaultsForGroup($group));
        }

        return $merged;
    }
}

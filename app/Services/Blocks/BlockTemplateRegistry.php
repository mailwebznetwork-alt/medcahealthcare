<?php

namespace App\Services\Blocks;

use Illuminate\Support\Facades\View;
use InvalidArgumentException;

class BlockTemplateRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return config('block_templates.templates', []);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function forCategories(array $categories): array
    {
        if ($categories === []) {
            return $this->all();
        }

        return array_filter(
            $this->all(),
            static fn (array $definition): bool => in_array($definition['category'] ?? '', $categories, true)
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function forSlugs(array $slugs): array
    {
        $all = $this->all();

        return array_filter(
            $all,
            static fn (string $slug): bool => in_array($slug, $slugs, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $slug): ?array
    {
        $definition = $this->all()[$slug] ?? null;

        return is_array($definition) ? $definition : null;
    }

    public function isManagedSlug(string $slug): bool
    {
        return $this->find($slug) !== null;
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    public function resolveCode(array $definition, string $slug): string
    {
        if (isset($definition['code']) && is_string($definition['code']) && trim($definition['code']) !== '') {
            return trim($definition['code']);
        }

        $view = $definition['view'] ?? null;
        if (! is_string($view) || trim($view) === '') {
            throw new InvalidArgumentException("Block template [{$slug}] has no view or code definition.");
        }

        if (! View::exists($view)) {
            throw new InvalidArgumentException("Block template view [{$view}] for [{$slug}] does not exist.");
        }

        return "@include('{$view}')";
    }

    public function assertViewExists(string $slug): void
    {
        $definition = $this->find($slug);
        if ($definition === null) {
            throw new InvalidArgumentException("Unknown block template slug [{$slug}].");
        }

        if (isset($definition['code']) && ! isset($definition['view'])) {
            return;
        }

        $view = $definition['view'] ?? null;
        if (! is_string($view) || ! View::exists($view)) {
            throw new InvalidArgumentException("Block template view missing for [{$slug}].");
        }
    }
}

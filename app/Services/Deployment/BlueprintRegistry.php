<?php

namespace App\Services\Deployment;

class BlueprintRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return config('blueprints', []);
    }

    /**
     * @return list<string>
     */
    public function slugs(): array
    {
        return array_keys($this->all());
    }

    /**
     * @param  array<string, mixed>|null
     */
    public function find(string $slug): ?array
    {
        $blueprint = $this->all()[$slug] ?? null;

        return is_array($blueprint) ? $blueprint : null;
    }

    /**
     * @return list<array{slug: string, label: string, industry: string}>
     */
    public function forIndustry(?string $industry): array
    {
        return collect($this->all())
            ->filter(fn (array $bp): bool => $industry === null || ($bp['industry'] ?? '') === $industry)
            ->map(fn (array $bp, string $slug): array => [
                'slug' => $slug,
                'label' => (string) ($bp['label'] ?? $slug),
                'industry' => (string) ($bp['industry'] ?? ''),
            ])
            ->values()
            ->all();
    }
}

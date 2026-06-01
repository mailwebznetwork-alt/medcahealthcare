<?php

namespace App\Services\Deployment;

class StylePackRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return config('style_packs', []);
    }

    /**
     * @return list<string>
     */
    public function slugs(): array
    {
        return array_keys($this->all());
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $slug): ?array
    {
        $pack = $this->all()[$slug] ?? null;

        return is_array($pack) ? $pack : null;
    }

    public function label(string $slug): string
    {
        return (string) ($this->find($slug)['label'] ?? $slug);
    }

    /**
     * @return array<string, string>
     */
    public function assignments(string $slug): array
    {
        $pack = $this->find($slug);

        return is_array($pack['assignments'] ?? null) ? $pack['assignments'] : [];
    }
}

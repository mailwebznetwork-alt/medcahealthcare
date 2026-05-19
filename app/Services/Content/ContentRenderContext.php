<?php

namespace App\Services\Content;

/**
 * Request-scoped variables merged into every block Blade render (e.g. $vacancies on /careers).
 */
class ContentRenderContext
{
    /** @var array<string, mixed> */
    private array $variables = [];

    /**
     * @param  array<string, mixed>  $variables
     */
    public function set(array $variables): void
    {
        $this->variables = $variables;
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    public function merge(array $variables): void
    {
        $this->variables = array_merge($this->variables, $variables);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->variables;
    }

    public function flush(): void
    {
        $this->variables = [];
    }
}

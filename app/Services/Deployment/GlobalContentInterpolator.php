<?php

namespace App\Services\Deployment;

class GlobalContentInterpolator
{
    public function __construct(
        private readonly GlobalContentVariableRepository $variables,
    ) {}

    public function interpolate(?string $content): string
    {
        if ($content === null || trim($content) === '') {
            return '';
        }

        $map = $this->variables->resolved();
        if ($map === []) {
            return $content;
        }

        return (string) preg_replace_callback(
            '/\{\{\s*([a-z][a-z0-9_]*)\s*\}\}/',
            function (array $matches) use ($map): string {
                $key = $matches[1] ?? '';

                return $map[$key] ?? $matches[0];
            },
            $content
        );
    }
}

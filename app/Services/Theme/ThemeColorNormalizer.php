<?php

namespace App\Services\Theme;

class ThemeColorNormalizer
{
    /**
     * @param  array<string, string>  $tokens
     * @return array<string, string>
     */
    public function normalizeMany(array $tokens): array
    {
        $normalized = [];
        foreach ($tokens as $key => $value) {
            if (! is_string($value)) {
                continue;
            }
            $hex = $this->normalizeHex($value);
            if ($hex !== null) {
                $normalized[$key] = $hex;
            }
        }

        return $normalized;
    }

    public function normalizeHex(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (! str_starts_with($value, '#')) {
            $value = '#'.$value;
        }

        if (! preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value)) {
            return null;
        }

        $hex = ltrim($value, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return '#'.strtolower($hex);
    }
}

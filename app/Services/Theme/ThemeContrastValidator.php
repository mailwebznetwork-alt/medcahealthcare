<?php

namespace App\Services\Theme;

class ThemeContrastValidator
{
    /**
     * @return list<string> validation error messages
     */
    public function validatePublicTokens(array $tokens): array
    {
        $errors = [];

        foreach ($tokens as $key => $value) {
            if (! is_string($value) || ! $this->isValidColor($value)) {
                $errors[] = "Invalid color for {$key}.";

                continue;
            }

            if (str_starts_with($key, 'text_') && isset($tokens['surface']) && is_string($tokens['surface'])) {
                $ratio = $this->contrastRatio($value, $tokens['surface']);
                if ($ratio < 4.5) {
                    $errors[] = "Contrast too low for {$key} on surface (ratio {$ratio}). Minimum 4.5:1.";
                }
            }
        }

        return $errors;
    }

    public function isValidColor(string $value): bool
    {
        return (bool) preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', trim($value));
    }

    public function contrastRatio(string $foreground, string $background): float
    {
        $l1 = $this->relativeLuminance($foreground);
        $l2 = $this->relativeLuminance($background);
        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return round(($lighter + 0.05) / ($darker + 0.05), 2);
    }

    private function relativeLuminance(string $hex): float
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $r = $r <= 0.03928 ? $r / 12.92 : (($r + 0.055) / 1.055) ** 2.4;
        $g = $g <= 0.03928 ? $g / 12.92 : (($g + 0.055) / 1.055) ** 2.4;
        $b = $b <= 0.03928 ? $b / 12.92 : (($b + 0.055) / 1.055) ** 2.4;

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
}

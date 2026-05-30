<?php

namespace App\Services\Theme;

class ThemeCssVariableBuilder
{
    /**
     * Map logical token keys to CSS custom property names on .medca-public-surface.
     *
     * @param  array<string, string>  $tokens
     * @return array<string, string> cssVar => value
     */
    public function publicVariables(array $tokens): array
    {
        $map = [
            'primary' => '--medca-primary',
            'primary_hover' => '--medca-primary-hover',
            'navy' => '--medca-navy',
            'navy_mid' => '--medca-navy-mid',
            'navy_border' => '--medca-navy-border',
            'navy_accent' => '--medca-navy-accent',
            'text_primary' => '--medca-text-primary',
            'text_secondary' => '--medca-text-secondary',
            'text_muted' => '--medca-text-muted',
            'surface' => '--medca-surface',
            'surface_muted' => '--medca-surface-muted',
            'surface_elevated' => '--medca-surface-elevated',
            'border' => '--medca-border',
            'success' => '--medca-success',
            'warning' => '--medca-warning',
            'danger' => '--medca-danger',
        ];

        $variables = [];
        foreach ($map as $key => $cssVar) {
            if (isset($tokens[$key]) && is_string($tokens[$key]) && $tokens[$key] !== '') {
                $variables[$cssVar] = $tokens[$key];
            }
        }

        if (isset($tokens['primary']) && is_string($tokens['primary'])) {
            $variables['--medca-primary-soft'] = $this->hexToRgba($tokens['primary'], 0.08);
            $variables['--medca-primary-border'] = $this->hexToRgba($tokens['primary'], 0.22);
        }

        return $variables;
    }

    /**
     * @return non-empty-string
     */
    public function inlineStyleBlock(array $variables, string $selector = 'body.medca-public-surface'): string
    {
        if ($variables === []) {
            return '';
        }

        $lines = collect($variables)
            ->map(fn (string $value, string $name): string => "    {$name}: {$value};")
            ->implode("\n");

        return "{$selector} {\n{$lines}\n}";
    }

    private function hexToRgba(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return sprintf('rgba(%d, %d, %d, %s)', $r, $g, $b, rtrim(rtrim(number_format($alpha, 2, '.', ''), '0'), '.'));
    }
}

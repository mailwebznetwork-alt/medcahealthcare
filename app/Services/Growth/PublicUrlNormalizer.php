<?php

namespace App\Services\Growth;

class PublicUrlNormalizer
{
    /**
     * @return list<string>
     */
    public function legacyHosts(): array
    {
        $configured = config('medca.legacy_url_hosts', []);

        return is_array($configured) ? array_values(array_filter($configured, 'is_string')) : [];
    }

    public function targetBase(?string $override = null): string
    {
        $base = $override ?? config('app.url');

        return rtrim(trim((string) $base), '/');
    }

    public function normalizeUrl(?string $url, ?string $targetBase = null, bool $rewriteNonTargetHosts = true): ?string
    {
        if ($url === null) {
            return null;
        }

        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }

        if (! str_starts_with($trimmed, 'http://') && ! str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }

        $parsed = parse_url($trimmed);
        if (! is_array($parsed) || ! isset($parsed['host'])) {
            return $trimmed;
        }

        $target = parse_url($this->targetBase($targetBase));
        if (! is_array($target) || ! isset($target['host'])) {
            return $trimmed;
        }

        $host = strtolower((string) $parsed['host']);
        $targetHost = strtolower((string) $target['host']);

        $isLegacy = in_array($host, array_map('strtolower', $this->legacyHosts()), true);
        $shouldRewrite = $isLegacy || ($rewriteNonTargetHosts && $host !== $targetHost);

        if (! $shouldRewrite) {
            return $trimmed;
        }

        $scheme = (string) ($target['scheme'] ?? 'https');
        $path = (string) ($parsed['path'] ?? '');
        $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';
        $fragment = isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';

        if ($path === '') {
            $path = '/';
        }

        return $scheme.'://'.$targetHost.$path.$query.$fragment;
    }

    /**
     * @param  array<string, mixed>|null  $hreflang
     * @return array<string, mixed>|null
     */
    public function normalizeHreflang(?array $hreflang, ?string $targetBase = null): ?array
    {
        if ($hreflang === null || $hreflang === []) {
            return $hreflang;
        }

        $normalized = [];
        foreach ($hreflang as $locale => $value) {
            if (is_string($value)) {
                $normalized[$locale] = $this->normalizeUrl($value, $targetBase) ?? $value;
            } else {
                $normalized[$locale] = $value;
            }
        }

        return $normalized;
    }

    public function normalizeRobotsTxt(?string $robots, ?string $targetBase = null): ?string
    {
        if ($robots === null || trim($robots) === '') {
            return $robots;
        }

        $lines = preg_split('/\r\n|\r|\n/', $robots) ?: [];
        $out = [];

        foreach ($lines as $line) {
            if (preg_match('/^\s*(Sitemap|Host)\s*:\s*(.+)$/i', $line, $matches) === 1) {
                $directive = $matches[1];
                $value = trim($matches[2]);
                $normalized = $this->normalizeUrl($value, $targetBase) ?? $value;
                $out[] = $directive.': '.$normalized;

                continue;
            }

            $out[] = $line;
        }

        return implode("\n", $out);
    }
}

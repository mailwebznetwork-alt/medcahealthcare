<?php

namespace App\Services\Marketing\Attribution;

use Illuminate\Http\Request;

class DeviceContextResolver
{
    /**
     * @return array{device_type: string, browser: string, operating_system: string}
     */
    public function resolve(?string $userAgent = null): array
    {
        $ua = mb_strtolower($userAgent ?? (string) request()->userAgent());

        $device = match (true) {
            str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone') => 'mobile',
            str_contains($ua, 'tablet') || str_contains($ua, 'ipad') => 'tablet',
            default => 'desktop',
        };

        $browser = match (true) {
            str_contains($ua, 'edg/') => 'Edge',
            str_contains($ua, 'chrome/') && ! str_contains($ua, 'edg/') => 'Chrome',
            str_contains($ua, 'firefox/') => 'Firefox',
            str_contains($ua, 'safari/') && ! str_contains($ua, 'chrome/') => 'Safari',
            default => 'Other',
        };

        $os = match (true) {
            str_contains($ua, 'windows') => 'Windows',
            str_contains($ua, 'mac os') || str_contains($ua, 'macintosh') => 'macOS',
            str_contains($ua, 'android') => 'Android',
            str_contains($ua, 'iphone') || str_contains($ua, 'ipad') => 'iOS',
            str_contains($ua, 'linux') => 'Linux',
            default => 'Other',
        };

        return [
            'device_type' => $device,
            'browser' => $browser,
            'operating_system' => $os,
        ];
    }

    /**
     * @return array{country: ?string, region: ?string, city: ?string}
     */
    public function geoFromRequest(Request $request): array
    {
        return [
            'country' => $this->sanitizeGeo($request->header('CF-IPCountry') ?? $request->header('X-Country-Code')),
            'region' => $this->sanitizeGeo($request->header('X-Region')),
            'city' => $this->sanitizeGeo($request->header('X-City')),
        ];
    }

    private function sanitizeGeo(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $clean = preg_replace('/[^a-zA-Z0-9\s\-_.]/', '', trim($value)) ?? '';

        return $clean !== '' ? mb_substr($clean, 0, 128) : null;
    }
}

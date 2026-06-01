<?php

namespace App\Services\Marketing\Attribution;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AttributionSessionStore
{
    /**
     * @return array<string, mixed>
     */
    public function firstTouch(): array
    {
        if (! config('marketing_automation.attribution.enabled', true)) {
            return [];
        }

        $raw = Cookie::get(config('marketing_automation.attribution.cookie_name'));
        if (! is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function lastTouch(Request $request): array
    {
        if (! $request->hasSession()) {
            return [];
        }

        $session = $request->session()->get(config('marketing_automation.attribution.session_key'));

        return is_array($session) ? $session : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function persistFirstTouch(array $payload): void
    {
        if ($payload === [] || $this->firstTouch() !== []) {
            return;
        }

        Cookie::queue(
            config('marketing_automation.attribution.cookie_name'),
            json_encode($payload),
            now()->addDays(config('marketing_automation.attribution.cookie_days', 90))->diffInMinutes(),
            '/',
            null,
            request()->isSecure(),
            false,
            false,
            'Lax'
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function persistLastTouch(Request $request, array $payload): void
    {
        if ($payload === [] || ! $request->hasSession()) {
            return;
        }

        $request->session()->put(config('marketing_automation.attribution.session_key'), $payload);
    }
}

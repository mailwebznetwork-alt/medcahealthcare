<?php

namespace App\Services\Marketing\Tracking;

use App\Models\MarketingClickEvent;
use App\Services\Marketing\Attribution\AttributionSessionStore;
use App\Services\Marketing\Attribution\DeviceContextResolver;
use Illuminate\Http\Request;

class MarketingClickTrackingService
{
    public function __construct(
        private readonly MarketingTrackingValidator $validator,
        private readonly DeviceContextResolver $deviceContext,
        private readonly AttributionSessionStore $attributionStore,
    ) {}

    /**
     * @return array{recorded: bool, id: ?int}
     */
    public function record(Request $request): array
    {
        if (! config('marketing_automation.enabled', true) || ! config('marketing_automation.click_tracking.enabled', true)) {
            return ['recorded' => false, 'id' => null];
        }

        $data = $this->validator->validate($request);
        $fingerprint = (string) ($data['session_fingerprint'] ?? '');
        if ($fingerprint === '' && $request->hasSession()) {
            $fingerprint = $request->session()->getId();
        }

        if ($this->validator->isDuplicate($fingerprint, $data['event_type'])) {
            return ['recorded' => false, 'id' => null];
        }

        $lastTouch = $this->attributionStore->lastTouch($request);
        $device = $this->deviceContext->resolve($request->userAgent());

        $event = MarketingClickEvent::query()->create([
            'event_type' => $data['event_type'],
            'page_path' => $data['page_path'] ?? '/'.ltrim($request->path(), '/'),
            'page_title' => $data['page_title'] ?? null,
            'campaign' => $data['campaign'] ?? ($lastTouch['utm_campaign'] ?? null),
            'source' => $data['source'] ?? ($lastTouch['utm_source'] ?? null),
            'medium' => $data['medium'] ?? ($lastTouch['utm_medium'] ?? null),
            'element_label' => $data['element_label'] ?? null,
            'destination_url' => $data['destination_url'] ?? null,
            'device_type' => $device['device_type'],
            'browser' => $device['browser'],
            'operating_system' => $device['operating_system'],
            'session_fingerprint' => $fingerprint !== '' ? $fingerprint : null,
            'meta' => $data['meta'] ?? null,
            'occurred_at' => now(),
        ]);

        return ['recorded' => true, 'id' => $event->id];
    }
}

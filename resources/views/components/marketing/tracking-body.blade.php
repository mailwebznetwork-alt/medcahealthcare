@props(['settings' => null])
@php
    $m = $settings ?? \App\Models\MarketingSetting::current();
    $ga4MeasurementId = $m->ga4_measurement_id;
    $metaPixelId = $m->meta_pixel_id;

    if (\Illuminate\Support\Facades\Schema::hasTable('integrations')) {
        $google = \App\Models\Integration::query()->where('name', 'google_services')->first();
        $meta = \App\Models\Integration::query()->where('name', 'meta_ads')->first();
        $googleCredentials = $google?->credentials ?? [];
        $metaCredentials = $meta?->credentials ?? [];

        $ga4MeasurementId = $googleCredentials['measurement_id'] ?? $ga4MeasurementId;
        $metaPixelId = $metaCredentials['pixel_id'] ?? $metaPixelId;
    }
@endphp
@if (filled($ga4MeasurementId) || filled($metaPixelId))
    <script>
        document.addEventListener('submit', function (e) {
            if (e.target && e.target.tagName === 'FORM' && typeof gtag === 'function') {
                gtag('event', 'form_submit');
            }
            if (e.target && e.target.tagName === 'FORM' && typeof fbq === 'function') {
                fbq('track', 'Lead');
            }
        }, true);
    </script>
@endif

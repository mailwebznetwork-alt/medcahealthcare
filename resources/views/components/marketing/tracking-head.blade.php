@props(['settings' => null])
@php
    $m = $settings ?? \App\Models\MarketingSetting::current();
    $ga4MeasurementId = $m->ga4_measurement_id;
    $googleAdsAwId = $m->google_ads_aw_id;
    $metaPixelId = $m->meta_pixel_id;

    if (\Illuminate\Support\Facades\Schema::hasTable('integrations')) {
        $google = \App\Models\Integration::query()->where('name', 'google_services')->first();
        $meta = \App\Models\Integration::query()->where('name', 'meta_ads')->first();
        $googleCredentials = $google?->credentials ?? [];
        $metaCredentials = $meta?->credentials ?? [];

        $ga4MeasurementId = $googleCredentials['measurement_id'] ?? $ga4MeasurementId;
        $googleAdsAwId = $googleCredentials['google_ads_aw_id'] ?? $googleAdsAwId;
        $metaPixelId = $metaCredentials['pixel_id'] ?? $metaPixelId;
    }
@endphp
@if (filled($ga4MeasurementId))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4MeasurementId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $ga4MeasurementId }}');
        @if (filled($googleAdsAwId))
        gtag('config', '{{ $googleAdsAwId }}');
        @endif
    </script>
@endif
@if (filled($metaPixelId))
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $metaPixelId }}');
        fbq('track', 'PageView');
    </script>
    <noscript>
        <img
            height="1"
            width="1"
            class="hidden"
            src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"
            alt=""
        />
    </noscript>
@endif

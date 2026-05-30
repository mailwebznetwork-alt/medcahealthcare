{{-- Site Architect → Insert service (adds {{service:code}} tokens). $services = only those codes. --}}
{{service:caregivers}}
{{service:homenursing-services}}
@include('public.services.partials.services-carousel', [
    'services' => $services,
    'sectionTitle' => __('Our clinical services'),
])

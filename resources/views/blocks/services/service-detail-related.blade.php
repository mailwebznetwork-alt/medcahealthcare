{{-- Insert service → adds {{service:code}} lines above. Carousel hidden until at least one token exists. --}}
@include('public.services.partials.services-carousel', [
    'services' => $services,
    'sectionTitle' => __('Related services'),
])

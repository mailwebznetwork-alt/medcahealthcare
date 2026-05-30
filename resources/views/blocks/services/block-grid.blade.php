{{-- Add {{service:your-code}} lines for each service in this category. --}}
@include('public.services.partials.services-grid', [
    'services' => $services,
    'sectionTitle' => __('All services'),
])

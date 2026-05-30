<x-public.section>
    <div data-service-detail-hero>
    <header>
        <h1>{{ $service->seo?->h1 ?: $service->title }}</h1>
        @if (filled($service->short_summary))
            <p>{{ $service->short_summary }}</p>
        @endif
    </header>
    @if (filled($service->description))
        <div>{!! $service->description !!}</div>
    @endif
    </div>
</x-public.section>

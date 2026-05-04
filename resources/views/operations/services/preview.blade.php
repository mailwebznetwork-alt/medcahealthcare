@php
    $seo = $service->seo;
@endphp

<x-app-layout :page-title="$service->title" :welcome-line="__('Preview — Enterprise service')">
    @push('head')
        @if ($seo?->meta_title)
            <meta name="title" content="{{ $seo->meta_title }}" />
        @endif
        @if ($seo?->meta_description)
            <meta name="description" content="{{ $seo->meta_description }}" />
        @endif
    @endpush

    <div class="mx-auto max-w-4xl space-y-8 px-4 py-6">
        <header class="space-y-2">
            <p class="mom-micro">{{ __('Service code') }}: <span class="font-mono text-mom-gold">{{ $service->service_code }}</span></p>
            <h1 class="mom-title-page">{{ $seo?->h1 ?: $service->title }}</h1>
            @if ($service->short_summary)
                <p class="mom-body-text text-lg">{{ $service->short_summary }}</p>
            @endif
        </header>

        @if ($seo?->h2 && count($seo->h2) > 0)
            <section>
                <h2 class="mom-section-title mb-3">{{ __('Section headings (H2)') }}</h2>
                <ul class="mom-body-text list-disc space-y-2 pl-5">
                    @foreach ($seo->h2 as $line)
                        @if (filled($line))
                            <li>{{ $line }}</li>
                        @endif
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($seo?->h3 && count($seo->h3) > 0)
            <section>
                <h2 class="mom-section-title mb-3">{{ __('Subheadings (H3)') }}</h2>
                <ul class="mom-body-text list-disc space-y-2 pl-5">
                    @foreach ($seo->h3 as $line)
                        @if (filled($line))
                            <li>{{ $line }}</li>
                        @endif
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($service->description)
            <section class="mom-card p-6">
                <h2 class="mom-section-title mb-3">{{ __('Description') }}</h2>
                <div class="mom-body-text prose prose-invert max-w-none whitespace-pre-wrap">{{ $service->description }}</div>
            </section>
        @endif

        @if ($service->faqs->isNotEmpty())
            <section class="mom-card p-6">
                <h2 class="mom-section-title mb-4">{{ __('FAQ') }}</h2>
                <dl class="space-y-4">
                    @foreach ($service->faqs as $faq)
                        <div class="border-b border-[color:var(--border-tabstrip-divider)] pb-4 last:border-0">
                            <dt class="font-semibold text-[var(--text-primary)]">{{ $faq->question }}</dt>
                            <dd class="mom-body-text mt-2 whitespace-pre-wrap">{{ $faq->answer }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif

        @if ($seo?->ai_context)
            <section class="mom-card p-6">
                <h2 class="mom-section-title mb-3">{{ __('AI context') }}</h2>
                <p class="mom-body-text whitespace-pre-wrap">{{ $seo->ai_context }}</p>
            </section>
        @endif

        @if ($seo?->search_intent)
            <section class="mom-card p-6">
                <h2 class="mom-section-title mb-3">{{ __('Search intent') }}</h2>
                <p class="mom-body-text">{{ $seo->search_intent }}</p>
            </section>
        @endif

        <section class="mom-card p-6">
            <h2 class="mom-section-title mb-3">{{ __('GEO — covered pincodes') }}</h2>
            @if ($service->pincodes->isEmpty())
                <p class="mom-body-text text-[var(--text-muted)]">{{ __('None selected.') }}</p>
            @else
                <ul class="mom-body-text grid gap-2 sm:grid-cols-2">
                    @foreach ($service->pincodes as $pc)
                        <li class="rounded-mom-chrome border border-[rgba(255,255,255,0.06)] px-3 py-2 font-mono text-sm">
                            {{ $pc->pincode }} — {{ $pc->area_name }}, {{ $pc->city }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="flex gap-3">
            <a href="{{ route('operations.services.edit', $service) }}" class="mom-cta-primary">{{ __('Edit service') }}</a>
            <a href="{{ route('operations.services.index') }}" class="mom-cta-ghost">{{ __('Back to list') }}</a>
        </div>
    </div>

    @if ($service->schema && filled($service->schema->schema_json))
        @push('scripts')
            <script type="application/ld+json">{!! json_encode($service->schema->schema_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
        @endpush
    @endif
</x-app-layout>

@php
    /** @var \App\Models\Vacancy $vacancy */
@endphp

<article class="w-full px-6 py-10 md:px-12" data-careers-job-detail>
    <header class="mx-auto max-w-3xl border-b border-slate-200 pb-8">
        <p class="text-xs text-slate-500">{{ config('careers.organization_name') }}</p>
        <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $vacancy->title }}</h1>
        <p class="mt-3 text-sm text-slate-600">
            {{ $vacancy->employment_type->label() }}
            @if ($vacancy->department)
                · {{ $vacancy->department }}
            @endif
            @if ($vacancy->city)
                · {{ $vacancy->city }}
            @endif
            @if ($vacancy->area)
                · {{ $vacancy->area }}
            @endif
            @if ($vacancy->pin_code)
                · {{ $vacancy->pin_code }}
            @endif
        </p>
        @if ($vacancy->closing_date)
            <p class="mt-4 text-xs text-slate-500">{{ __('Apply before :date', ['date' => $vacancy->closing_date->format('Y-m-d')]) }}</p>
        @endif
    </header>

    @if (session('status') === 'application-received')
        <p class="mx-auto mt-8 max-w-5xl text-sm text-emerald-700" role="status">{{ __('Thank you — your application was received.') }}</p>
    @endif

    <div class="mx-auto mt-10 grid max-w-5xl grid-cols-1 gap-10 lg:grid-cols-3">
        <div class="space-y-8 lg:col-span-2">
            @if ($vacancy->summary)
                <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Overview') }}</h2>
                    <div class="mt-4 whitespace-pre-wrap text-sm text-slate-600">{{ $vacancy->summary }}</div>
                </section>
            @endif
            @if ($vacancy->description)
                <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Role description') }}</h2>
                    <div class="mt-4 whitespace-pre-wrap text-sm text-slate-600">{{ $vacancy->description }}</div>
                </section>
            @endif
            @if ($vacancy->requirements)
                <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Requirements') }}</h2>
                    <div class="mt-4 whitespace-pre-wrap text-sm text-slate-600">{{ $vacancy->requirements }}</div>
                </section>
            @endif
        </div>
        <aside class="lg:sticky lg:top-24 lg:self-start">
            @include('careers.partials.apply-panel', ['vacancy' => $vacancy])
        </aside>
    </div>
</article>

@props([
    'pageTitle' => null,
    'welcomeLine' => null,
    'activeTab' => null,
])

@php
    $resolvedPageTitle = $pageTitle ?? __('Growth Center');
    $resolvedWelcome = $welcomeLine ?? __('Competitor intelligence, SEO, coverage, and analytics.');
    $tab = $activeTab ?? (string) request()->query('tab', 'competitors');
@endphp

<x-app-layout
    :page-title="$resolvedPageTitle"
    :welcome-line="$resolvedWelcome"
>
    <div class="operations-workspace">
        <div class="mom-backend-tabstrip">
            @include('growth-center.partials.primary-tabs', ['activeTab' => $tab])
        </div>

        <div class="mt-10">
            {{ $slot }}
        </div>
    </div>
</x-app-layout>

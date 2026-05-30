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

<x-admin.workspace
    :page-title="$resolvedPageTitle"
    :welcome-line="$resolvedWelcome"
>
    <x-slot:tabs>
        @include('growth-center.partials.primary-tabs', ['activeTab' => $tab])
    </x-slot:tabs>

    {{ $slot }}
</x-admin.workspace>

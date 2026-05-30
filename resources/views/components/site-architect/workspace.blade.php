@props([
    'pageTitle' => null,
    'welcomeLine' => null,
])

@php
    $resolvedPageTitle = $pageTitle ?? __('Site Architect');
    $resolvedWelcome = $welcomeLine ?? __('Structure-only content, reusable blocks, PIN-code GEO.');
@endphp

<x-admin.workspace
    :page-title="$resolvedPageTitle"
    :welcome-line="$resolvedWelcome"
>
    <x-slot:tabs>
        @include('site-architect.partials.primary-tabs')
    </x-slot:tabs>

    {{ $slot }}
</x-admin.workspace>

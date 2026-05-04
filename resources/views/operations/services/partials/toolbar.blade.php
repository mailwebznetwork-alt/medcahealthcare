@php
    $indexActive = request()->routeIs('operations.services.index');
    $editing = request()->routeIs('operations.services.create', 'operations.services.edit', 'operations.services.preview');
@endphp

<nav class="flex flex-wrap gap-3" aria-label="{{ __('Services') }}">
    <a href="{{ route('operations.services.create') }}" class="mom-cta-primary">{{ __('Create service') }}</a>
    <a
        href="{{ route('operations.services.index') }}"
        @class([
            'mom-cta-ghost',
            'mom-cta-ghost--active' => $indexActive && ! $editing,
        ])
    >{{ __('All services') }}</a>
</nav>

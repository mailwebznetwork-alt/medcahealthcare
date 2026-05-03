@php
    $tabs = [
        ['label' => __('Overview'), 'route' => 'operations.job-portal.index', 'pattern' => 'operations.job-portal.index'],
        ['label' => __('Vacancies'), 'route' => 'operations.job-portal.vacancies.index', 'pattern' => 'operations.job-portal.vacancies.*'],
        ['label' => __('Applications'), 'route' => 'operations.job-portal.applications.index', 'pattern' => 'operations.job-portal.applications.*'],
    ];
@endphp

<nav class="mb-8 flex flex-wrap gap-2 border-b border-[rgba(255,255,255,0.045)] pb-4" aria-label="{{ __('Job portal sections') }}">
    @foreach ($tabs as $tab)
        @php
            $active = request()->routeIs($tab['pattern']);
        @endphp
        <a
            href="{{ route($tab['route']) }}"
            @class([
                'rounded-mom-pill px-4 py-2 text-sm font-semibold transition-all duration-320 ease-premium',
                'bg-[rgba(212,169,95,0.12)] text-mom-gold ring-1 ring-[rgba(212,169,95,0.22)]' => $active,
                'text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] hover:text-[var(--text-primary)]' => ! $active,
            ])
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>

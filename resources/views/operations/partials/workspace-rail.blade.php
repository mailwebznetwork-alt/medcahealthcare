@php
    $jobPortalLinks = [
        ['label' => __('Overview'), 'route' => 'operations.job-portal.index', 'pattern' => 'operations.job-portal.index', 'icon' => 'layout-dashboard'],
        ['label' => __('Vacancies'), 'route' => 'operations.job-portal.vacancies.index', 'pattern' => 'operations.job-portal.vacancies.*', 'icon' => 'briefcase-business'],
        ['label' => __('Applications'), 'route' => 'operations.job-portal.applications.index', 'pattern' => 'operations.job-portal.applications.*', 'icon' => 'inbox'],
    ];
    $pinCodeLinks = [
        ['label' => __('Directory'), 'route' => 'operations.pin-codes.index', 'pattern' => ['operations.pin-codes.index', 'operations.pin-codes.create', 'operations.pin-codes.edit'], 'icon' => 'map-pin'],
        ['label' => __('Bulk import'), 'route' => 'operations.pin-codes.import.create', 'pattern' => 'operations.pin-codes.import.*', 'icon' => 'upload'],
    ];
@endphp

<aside
    class="w-full shrink-0 rounded-mom-lg border border-[rgba(255,255,255,0.06)] bg-[rgba(10,15,28,0.55)] p-4 shadow-mom-inner lg:sticky lg:top-[88px] lg:w-[15.5rem] lg:self-start"
    aria-label="{{ __('Operations workspaces') }}"
>
    <p class="mom-micro mb-4 px-1">{{ __('Operations') }}</p>

    <div class="space-y-4">
        <div class="rounded-mom-md border border-[rgba(255,255,255,0.045)] bg-[rgba(255,255,255,0.02)] p-1">
            <p class="rounded-mom-sm px-3 py-2 text-xs font-semibold uppercase tracking-widest text-[var(--text-secondary)]">
                {{ __('Job Portal') }}
            </p>

            <ul class="mt-1 space-y-0.5 border-t border-[rgba(255,255,255,0.045)] pt-1" role="list">
                @foreach ($jobPortalLinks as $tab)
                    @php
                        $active = is_array($tab['pattern'])
                            ? request()->routeIs(...$tab['pattern'])
                            : request()->routeIs($tab['pattern']);
                    @endphp
                    <li>
                        <a
                            href="{{ route($tab['route']) }}"
                            @class([
                                'flex items-center gap-2.5 rounded-mom-sm px-3 py-2 text-sm font-medium transition-all duration-320 ease-premium',
                                'bg-[rgba(212,169,95,0.12)] text-mom-gold ring-1 ring-[rgba(212,169,95,0.2)]' => $active,
                                'text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] hover:text-[var(--text-primary)]' => ! $active,
                            ])
                        >
                            <i data-lucide="{{ $tab['icon'] }}" class="h-4 w-4 shrink-0 opacity-90"></i>
                            <span>{{ $tab['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="rounded-mom-md border border-[rgba(255,255,255,0.045)] bg-[rgba(255,255,255,0.02)] p-1">
            <p class="rounded-mom-sm px-3 py-2 text-xs font-semibold uppercase tracking-widest text-[var(--text-secondary)]">
                {{ __('Pin Codes') }}
            </p>

            <ul class="mt-1 space-y-0.5 border-t border-[rgba(255,255,255,0.045)] pt-1" role="list">
                @foreach ($pinCodeLinks as $tab)
                    @php
                        $active = is_array($tab['pattern'])
                            ? request()->routeIs(...$tab['pattern'])
                            : request()->routeIs($tab['pattern']);
                    @endphp
                    <li>
                        <a
                            href="{{ route($tab['route']) }}"
                            @class([
                                'flex items-center gap-2.5 rounded-mom-sm px-3 py-2 text-sm font-medium transition-all duration-320 ease-premium',
                                'bg-[rgba(212,169,95,0.12)] text-mom-gold ring-1 ring-[rgba(212,169,95,0.2)]' => $active,
                                'text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] hover:text-[var(--text-primary)]' => ! $active,
                            ])
                        >
                            <i data-lucide="{{ $tab['icon'] }}" class="h-4 w-4 shrink-0 opacity-90"></i>
                            <span>{{ $tab['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</aside>

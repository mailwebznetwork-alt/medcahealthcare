@php
    $steps = [
        ['route' => 'settings.appearance', 'label' => __('Theme & header'), 'hint' => __('Appearance')],
        ['route' => 'site-architect.blueprint-builder.index', 'label' => __('Blueprint'), 'hint' => __('Generate pages')],
        ['route' => 'site-architect.section-library.index', 'label' => __('Sections'), 'hint' => __('Library')],
        ['route' => 'site-architect.block-presets.index', 'label' => __('Presets'), 'hint' => __('Blocks')],
        ['route' => 'site-architect.block-studio.index', 'label' => __('Block studio'), 'hint' => __('Media & layout')],
        ['route' => 'settings.global-content', 'label' => __('Global content'), 'hint' => __('Variables')],
        ['route' => 'site-architect.deployment-packages.index', 'label' => __('Packages'), 'hint' => __('Deploy')],
    ];
@endphp
<nav class="mb-6 rounded-xl border border-[var(--border-panel-soft)] bg-[var(--bg-surface)] p-4" aria-label="{{ __('Deployment workflow') }}">
    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-mom-gold">{{ __('MarkOnMinds Deployment Engine') }}</p>
    <ol class="flex flex-wrap gap-2">
        @foreach ($steps as $step)
            <li>
                <a
                    href="{{ route($step['route']) }}"
                    @class([
                        'inline-flex flex-col rounded-lg border px-3 py-2 text-xs transition',
                        'border-mom-gold bg-mom-gold/10 text-mom-gold' => request()->routeIs($step['route'].'*') || request()->routeIs($step['route']),
                        'border-[var(--border-panel-soft)] text-[var(--text-secondary)] hover:border-mom-gold/50 hover:text-[var(--text-primary)]' => ! request()->routeIs($step['route'].'*') && ! request()->routeIs($step['route']),
                    ])
                >
                    <span class="font-semibold">{{ $step['label'] }}</span>
                    <span class="opacity-80">{{ $step['hint'] }}</span>
                </a>
            </li>
        @endforeach
    </ol>
</nav>

@php
    $t = $activeTab ?? 'competitors';
@endphp

<nav class="flex flex-wrap gap-0" aria-label="{{ __('Growth Center workspaces') }}">
    <a
        href="{{ route('growth-center.competitors.index', ['tab' => 'readiness']) }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $t === 'readiness',
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => $t !== 'readiness',
        ])
    >{{ __('Readiness') }}</a>
    <a
        href="{{ route('growth-center.competitors.index', ['tab' => 'war-room']) }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $t === 'war-room',
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => $t !== 'war-room',
        ])
    >{{ __('War Room') }}</a>
    <a
        href="{{ route('growth-center.competitors.index', ['tab' => 'hijack-opportunities']) }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $t === 'hijack-opportunities',
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => $t !== 'hijack-opportunities',
        ])
    >{{ __('Hijack Ops') }}</a>
    <a
        href="{{ route('growth-center.competitors.index', ['tab' => 'seo']) }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $t === 'seo',
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => $t !== 'seo',
        ])
    >{{ __('SEO') }}</a>
    <a
        href="{{ route('growth-center.competitors.index', ['tab' => 'ga4']) }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $t === 'ga4',
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => $t !== 'ga4',
        ])
    >{{ __('GA4') }}</a>
    <a
        href="{{ route('growth-center.competitors.index', ['tab' => 'ai-pulse']) }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $t === 'ai-pulse',
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => $t !== 'ai-pulse',
        ])
    >{{ __('AI Pulse') }}</a>
    <a
        href="{{ route('growth-center.competitors.index', ['tab' => 'competitors']) }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $t === 'competitors',
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => $t !== 'competitors',
        ])
    >{{ __('Competitors') }}</a>
</nav>

@php
    $pagesActive = request()->routeIs('site-architect.pages.*');
    $blogsActive = request()->routeIs('site-architect.blogs.*');
    $blockFactoryActive = request()->routeIs('site-architect.block-factory.*');
    $blueprintBuilderActive = request()->routeIs('site-architect.blueprint-builder.*');
    $sectionLibraryActive = request()->routeIs('site-architect.section-library.*');
    $blockPresetsActive = request()->routeIs('site-architect.block-presets.*');
    $blockStudioActive = request()->routeIs('site-architect.block-studio.*');
    $deploymentPackagesActive = request()->routeIs('site-architect.deployment-packages.*');
    $mediaActive = request()->routeIs('site-architect.media.*');
    $modulesActive = request()->routeIs('site-architect.modules.*');
    $navigationActive = request()->routeIs('site-architect.navigation.*');
@endphp

<nav class="flex flex-wrap gap-0" aria-label="{{ __('Site Architect workspaces') }}">
    <a
        href="{{ route('site-architect.pages.index') }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $pagesActive,
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => ! $pagesActive,
        ])
    >{{ __('Pages') }}</a>
    <a
        href="{{ route('site-architect.blueprint-builder.index') }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $blueprintBuilderActive,
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => ! $blueprintBuilderActive,
        ])
    >{{ __('Blueprint Builder') }}</a>
    <a
        href="{{ route('site-architect.section-library.index') }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $sectionLibraryActive,
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => ! $sectionLibraryActive,
        ])
    >{{ __('Sections') }}</a>
    <a
        href="{{ route('site-architect.block-presets.index') }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $blockPresetsActive,
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => ! $blockPresetsActive,
        ])
    >{{ __('Presets') }}</a>
    <a
        href="{{ route('site-architect.block-studio.index') }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $blockStudioActive,
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => ! $blockStudioActive,
        ])
    >{{ __('Block Studio') }}</a>
    <a
        href="{{ route('site-architect.deployment-packages.index') }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $deploymentPackagesActive,
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => ! $deploymentPackagesActive,
        ])
    >{{ __('Packages') }}</a>
    <a
        href="{{ route('site-architect.navigation.index') }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $navigationActive,
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => ! $navigationActive,
        ])
    >{{ __('Navigation') }}</a>
    <a
        href="{{ route('site-architect.blogs.index') }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $blogsActive,
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => ! $blogsActive,
        ])
    >{{ __('Blogs') }}</a>
    <a
        href="{{ route('site-architect.block-factory.index') }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $blockFactoryActive,
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => ! $blockFactoryActive,
        ])
    >{{ __('Block Factory') }}</a>
    <a
        href="{{ route('site-architect.media.index') }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $mediaActive,
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => ! $mediaActive,
        ])
    >{{ __('Media') }}</a>
    <a
        href="{{ route('site-architect.modules.index') }}"
        @class([
            'inline-flex items-center border-b px-5 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-320 ease-premium',
            'border-mom-gold text-mom-gold' => $modulesActive,
            'border-transparent text-[var(--text-secondary)] hover:border-[var(--border-panel-soft)] hover:text-[var(--text-primary)]' => ! $modulesActive,
        ])
    >{{ __('Module Builder') }}</a>
</nav>

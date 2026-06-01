<div class="space-y-6">
    @if (! $ready)
        <x-admin.card><p class="mom-body-text">{{ __('Run migrations for Deployment Packages.') }}</p></x-admin.card>
    @else
        @if ($statusMessage)<p class="mom-body-text text-[var(--success)]">{{ $statusMessage }}</p>@endif
        @if ($errorMessage)<p class="mom-body-text text-[var(--danger)]">{{ $errorMessage }}</p>@endif

        <x-admin.card :title="__('Export deployment package')">
            <p class="mom-body-text mb-4 text-[var(--text-secondary)]">{{ __('Bundles theme, style pack, blueprints, sections, block presets, media mapping, and global variables.') }}</p>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="block"><span class="mom-label">{{ __('Package name') }}</span><input type="text" wire:model="package_name" class="mom-input w-full" /></label>
                <label class="block"><span class="mom-label">{{ __('Style pack') }}</span>
                    <select wire:model="style_pack_slug" class="mom-input w-full">
                        @foreach ($stylePackOptions as $slug => $pack)<option value="{{ $slug }}">{{ $pack['label'] ?? $slug }}</option>@endforeach
                    </select>
                </label>
                <label class="block md:col-span-2"><span class="mom-label">{{ __('Blueprint slugs (comma-separated)') }}</span><input type="text" wire:model="blueprint_slugs" class="mom-input w-full" /></label>
            </div>
            @if ($sectionOptions->isNotEmpty())
                <div class="mt-4">
                    <span class="mom-label">{{ __('Sections to include') }}</span>
                    <div class="mt-2 flex max-h-32 flex-wrap gap-2 overflow-y-auto custom-scrollbar">
                        @foreach ($sectionOptions as $section)
                            <label class="inline-flex items-center gap-1 text-xs"><input type="checkbox" wire:model="selected_section_slugs" value="{{ $section->slug }}" /> {{ $section->name }}</label>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($presetOptions->isNotEmpty())
                <div class="mt-4">
                    <span class="mom-label">{{ __('Block presets to include') }}</span>
                    <div class="mt-2 flex max-h-32 flex-wrap gap-2 overflow-y-auto custom-scrollbar">
                        @foreach ($presetOptions as $preset)
                            <label class="inline-flex items-center gap-1 text-xs"><input type="checkbox" wire:model="selected_preset_slugs" value="{{ $preset->slug }}" /> {{ $preset->name }}</label>
                        @endforeach
                    </div>
                </div>
            @endif
            <button type="button" wire:click="exportPackage" class="mom-cta-compact mom-cta-primary mt-4">{{ __('Export package') }}</button>
        </x-admin.card>

        <x-admin.card :title="__('Import / validate / clone')">
            <textarea wire:model="import_json" rows="12" class="mom-input w-full font-mono text-xs"></textarea>
            <div class="mt-3 flex flex-wrap gap-2">
                <button type="button" wire:click="validateManifest" class="mom-cta-compact mom-cta-ghost">{{ __('Validate') }}</button>
                <button type="button" wire:click="importPackage" class="mom-cta-compact mom-cta-primary">{{ __('Import package') }}</button>
            </div>
            @if ($validation_report)
                <div class="mt-4 rounded-lg border border-[var(--border-panel-soft)] p-4 text-sm">
                    <p class="font-semibold {{ $validation_report['valid'] ? 'text-[var(--success)]' : 'text-[var(--danger)]' }}">
                        {{ $validation_report['valid'] ? __('Compatible') : __('Issues found') }}
                    </p>
                    @foreach ($validation_report['errors'] as $error)<p class="text-[var(--danger)]">• {{ $error }}</p>@endforeach
                    @foreach ($validation_report['warnings'] as $warning)<p class="text-[var(--text-secondary)]">• {{ $warning }}</p>@endforeach
                </div>
            @endif
        </x-admin.card>

        @if ($packages->isNotEmpty())
            <x-admin.card :title="__('Recent packages')">
                <ul class="space-y-2 text-sm text-[var(--text-secondary)]">
                    @foreach ($packages as $package)
                        <li class="flex flex-wrap items-center justify-between gap-2">
                            <span>{{ $package->name }} · v{{ $package->package_version }} · {{ $package->exported_at?->diffForHumans() }}</span>
                            <button type="button" wire:click="clonePackage({{ $package->id }})" class="mom-cta-compact mom-cta-ghost">{{ __('Clone') }}</button>
                        </li>
                    @endforeach
                </ul>
            </x-admin.card>
        @endif
    @endif
</div>

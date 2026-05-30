<div class="space-y-6">
    @if (! $configuration)
        <x-admin.card>
            <p class="mom-body-text text-[var(--text-secondary)]">{{ __('Run database migrations to enable the Theme Management system.') }}</p>
        </x-admin.card>
    @else
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                @foreach (['branding' => __('Branding'), 'colors' => __('Colors'), 'typography' => __('Typography'), 'buttons' => __('Buttons'), 'cards' => __('Cards'), 'header' => __('Header'), 'layout' => __('Layout'), 'presets' => __('Presets'), 'preview' => __('Preview')] as $key => $label)
                    <button
                        type="button"
                        wire:click="setTab('{{ $key }}')"
                        @class([
                            'mom-cta-compact',
                            'mom-cta-primary' => $activeTab === $key,
                            'mom-cta-ghost' => $activeTab !== $key,
                        ])
                    >{{ $label }}</button>
                @endforeach
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($previewActive)
                    <button type="button" wire:click="disablePreview" class="mom-cta-compact mom-cta-ghost">{{ __('Exit preview') }}</button>
                @else
                    <button type="button" wire:click="enablePreview" class="mom-cta-compact mom-cta-ghost">{{ __('Enable preview') }}</button>
                @endif
                <button type="button" wire:click="resetDraft" wire:confirm="{{ __('Discard all draft changes?') }}" class="mom-cta-compact mom-cta-ghost">{{ __('Reset draft') }}</button>
                @if ($canPublish)
                    <button type="button" wire:click="publish" wire:confirm="{{ __('Publish draft theme to the live public site?') }}" class="mom-cta-compact mom-cta-primary">{{ __('Publish') }}</button>
                @endif
            </div>
        </div>

        @if (session('status'))
            <p class="mom-body-text text-[var(--success)]" role="status">{{ session('status') }}</p>
        @endif

        @if ($activeTab === 'branding')
            <x-admin.card :title="__('Branding')">
                <form wire:submit="saveBranding" class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mom-label">{{ __('Company name') }}</span>
                        <input type="text" wire:model="branding.brand_name" class="mom-input w-full" />
                        @error('branding.brand_name') <span class="text-[var(--danger)] text-xs">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="mom-label">{{ __('Tagline') }}</span>
                        <input type="text" wire:model="branding.tagline" class="mom-input w-full" />
                    </label>
                    <label class="block">
                        <span class="mom-label">{{ __('Contact email') }}</span>
                        <input type="email" wire:model="branding.contact_email" class="mom-input w-full" />
                    </label>
                    <label class="block">
                        <span class="mom-label">{{ __('Brand URL') }}</span>
                        <input type="url" wire:model="branding.brand_url" class="mom-input w-full" />
                    </label>
                    <label class="block">
                        <span class="mom-label">{{ __('WhatsApp URL') }}</span>
                        <input type="url" wire:model="branding.whatsapp_url" class="mom-input w-full" />
                    </label>
                    <label class="block">
                        <span class="mom-label">{{ __('Primary CTA text') }}</span>
                        <input type="text" wire:model="branding.primary_cta_text" class="mom-input w-full" />
                    </label>
                    <label class="block">
                        <span class="mom-label">{{ __('Phone display') }}</span>
                        <input type="text" wire:model="branding.phone_display" class="mom-input w-full" />
                    </label>
                    <label class="block">
                        <span class="mom-label">{{ __('Phone tel') }}</span>
                        <input type="text" wire:model="branding.phone_tel" class="mom-input w-full" />
                    </label>
                    <label class="block md:col-span-2">
                        <span class="mom-label">{{ __('Logo upload') }}</span>
                        <input type="file" wire:model="logo_upload" accept="image/*" class="mom-input w-full" />
                        @if ($logoUrl)<img src="{{ $logoUrl }}" alt="" class="mt-2 h-10 object-contain" />@endif
                    </label>
                    <label class="block md:col-span-2">
                        <span class="mom-label">{{ __('Favicon upload') }}</span>
                        <input type="file" wire:model="favicon_upload" accept="image/*" class="mom-input w-full" />
                        @if ($faviconUrl)<img src="{{ $faviconUrl }}" alt="" class="mt-2 h-8 w-8 object-contain" />@endif
                    </label>
                    <div class="md:col-span-2">
                        <button type="submit" class="mom-cta-compact mom-cta-primary">{{ __('Save branding draft') }}</button>
                    </div>
                </form>
            </x-admin.card>
        @endif

        @if ($activeTab === 'colors')
            <x-admin.card :title="__('Colors')">
                <form wire:submit="saveColors" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($tokenKeys as $key)
                        <label class="block">
                            <span class="mom-label">{{ str_replace('_', ' ', ucfirst($key)) }}</span>
                            <div class="flex items-center gap-2">
                                <input type="color" wire:model.live="tokens.{{ $key }}" class="h-10 w-14 cursor-pointer rounded border border-[var(--border-panel-soft)]" />
                                <input type="text" wire:model="tokens.{{ $key }}" class="mom-input flex-1 font-mono text-xs" />
                            </div>
                        </label>
                    @endforeach
                    <div class="sm:col-span-2 lg:col-span-3">
                        @if ($preview['contrast_errors'] !== [])
                            <ul class="mb-3 list-disc pl-5 text-xs text-[var(--danger)]">
                                @foreach ($preview['contrast_errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <button type="submit" class="mom-cta-compact mom-cta-primary">{{ __('Save color draft') }}</button>
                    </div>
                </form>
            </x-admin.card>
        @endif

        @if ($activeTab === 'typography')
            <x-admin.card :title="__('Typography')">
                <form wire:submit="saveTypography" class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mom-label">{{ __('Heading font') }}</span>
                        <select wire:model="typography.heading_font" class="mom-input w-full">
                            @foreach ($fontWhitelist as $font)
                                <option value="{{ $font }}">{{ $font }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="mom-label">{{ __('Body font') }}</span>
                        <select wire:model="typography.body_font" class="mom-input w-full">
                            @foreach ($fontWhitelist as $font)
                                <option value="{{ $font }}">{{ $font }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="mom-label">{{ __('Line height') }}</span>
                        <input type="text" wire:model="typography.line_height" class="mom-input w-full" />
                    </label>
                    <label class="block">
                        <span class="mom-label">{{ __('Letter spacing') }}</span>
                        <input type="text" wire:model="typography.letter_spacing" class="mom-input w-full" />
                    </label>
                    <div class="md:col-span-2">
                        <button type="submit" class="mom-cta-compact mom-cta-primary">{{ __('Save typography draft') }}</button>
                    </div>
                </form>
            </x-admin.card>
        @endif

        @if ($activeTab === 'buttons')
            <x-admin.card :title="__('Button preview')">
                <div class="flex flex-wrap gap-3 rounded-xl border border-[var(--border-panel-soft)] bg-[var(--bg-surface)] p-6" style="{!! collect($preview['css'] ? [] : [])->implode('') !!}">
                    @if ($preview['css'])
                        <style>{!! $preview['css'] !!}</style>
                    @endif
                    <button type="button" class="btn-premium">{{ $branding['primary_cta_text'] ?? __('Book a consultation') }}</button>
                    <button type="button" class="medca-cta-solid">{{ __('Solid CTA') }}</button>
                    <button type="button" class="rounded-lg border border-medca-border px-4 py-2 text-sm text-medca-primary">{{ __('Outline') }}</button>
                </div>
            </x-admin.card>
        @endif

        @if ($activeTab === 'cards')
            <x-admin.card :title="__('Card preview')">
                @if ($preview['css'])<style>{!! $preview['css'] !!}</style>@endif
                <div class="grid gap-4 md:grid-cols-2">
                    <x-public.card title="{{ __('Sample card') }}">
                        <p class="text-sm text-medca-text-secondary">{{ __('Cards inherit --medca-* tokens automatically.') }}</p>
                    </x-public.card>
                    <div class="service-card p-6">
                        <h3 class="text-lg font-semibold text-medca-text-primary">{{ __('Service card') }}</h3>
                        <p class="mt-2 text-sm text-medca-text-muted">{{ __('Legacy service-card class mapped to tokens.') }}</p>
                    </div>
                </div>
            </x-admin.card>
        @endif

        @if ($activeTab === 'header')
            <x-admin.card :title="__('Header presets')">
                <form wire:submit="saveHeader" class="space-y-4">
                    <div class="grid gap-3 md:grid-cols-2">
                        @foreach ($headerPresets as $slug => $preset)
                            <label @class(['block cursor-pointer rounded-xl border p-4 transition', 'border-mom-gold bg-[var(--bg-surface)]' => $header_preset === $slug, 'border-[var(--border-panel-soft)]' => $header_preset !== $slug])>
                                <input type="radio" wire:model="header_preset" value="{{ $slug }}" class="sr-only" />
                                <span class="font-semibold text-[var(--text-primary)]">{{ $preset['label'] }}</span>
                                <p class="mt-1 text-xs text-[var(--text-secondary)]">{{ $preset['description'] }}</p>
                            </label>
                        @endforeach
                    </div>
                    <button type="submit" class="mom-cta-compact mom-cta-primary">{{ __('Save header draft') }}</button>
                </form>
            </x-admin.card>
        @endif

        @if ($activeTab === 'layout')
            <x-admin.card :title="__('Layout width')">
                <form wire:submit="saveLayout" class="space-y-4">
                    <div class="grid gap-3 md:grid-cols-3">
                        @foreach ($layoutPresets as $slug => $preset)
                            <label @class(['block cursor-pointer rounded-xl border p-4', 'border-mom-gold' => $layout_preset === $slug])>
                                <input type="radio" wire:model="layout_preset" value="{{ $slug }}" class="sr-only" />
                                <span class="font-semibold">{{ $preset['label'] }}</span>
                                <p class="mt-1 text-xs text-[var(--text-secondary)]"><code>{{ $preset['shell_class'] }}</code></p>
                            </label>
                        @endforeach
                    </div>
                    <button type="submit" class="mom-cta-compact mom-cta-primary">{{ __('Save layout draft') }}</button>
                </form>
            </x-admin.card>
        @endif

        @if ($activeTab === 'presets')
            <x-admin.card :title="__('Theme presets')">
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mom-label">{{ __('Select preset') }}</span>
                        <select wire:model="preset_slug" class="mom-input w-full">
                            @foreach ($presets as $preset)
                                <option value="{{ $preset->slug }}">{{ $preset->name }}@if($preset->is_builtin) ({{ __('Built-in') }})@endif</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="mom-label">{{ __('Clone as') }}</span>
                        <input type="text" wire:model="clone_name" class="mom-input w-full" placeholder="{{ __('My custom preset') }}" />
                    </label>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button" wire:click="applyPreset" class="mom-cta-compact mom-cta-primary">{{ __('Apply to draft') }}</button>
                    <button type="button" wire:click="clonePreset" class="mom-cta-compact mom-cta-ghost">{{ __('Clone preset') }}</button>
                    <button type="button" wire:click="exportPreset" class="mom-cta-compact mom-cta-ghost">{{ __('Export JSON') }}</button>
                </div>
                <label class="mt-6 block">
                    <span class="mom-label">{{ __('Import / export JSON') }}</span>
                    <textarea wire:model="import_json" rows="8" class="mom-input w-full font-mono text-xs"></textarea>
                </label>
                <button type="button" wire:click="importPreset" class="mom-cta-compact mom-cta-ghost mt-2">{{ __('Import preset') }}</button>
            </x-admin.card>
        @endif

        @if ($activeTab === 'preview')
            <x-admin.card :title="__('Live preview summary')">
                @if ($preview['css'])<style>{!! $preview['css'] !!}</style>@endif
                <dl class="grid gap-2 text-sm md:grid-cols-2">
                    <div><dt class="text-[var(--text-secondary)]">{{ __('Header') }}</dt><dd>{{ $headerPresets[$header_preset]['label'] ?? $header_preset }}</dd></div>
                    <div><dt class="text-[var(--text-secondary)]">{{ __('Layout') }}</dt><dd>{{ $layoutPresets[$layout_preset]['label'] ?? $layout_preset }}</dd></div>
                    <div><dt class="text-[var(--text-secondary)]">{{ __('Draft updated') }}</dt><dd>{{ $configuration->draft_updated_at?->diffForHumans() ?? '—' }}</dd></div>
                    <div><dt class="text-[var(--text-secondary)]">{{ __('Last published') }}</dt><dd>{{ $configuration->published_at?->diffForHumans() ?? '—' }}</dd></div>
                </dl>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="mom-cta-compact mom-cta-primary">{{ __('Open public site') }}</a>
                    <button type="button" wire:click="enablePreview" class="mom-cta-compact mom-cta-ghost">{{ __('Enable session preview') }}</button>
                </div>
                <p class="mom-body-text mt-4 text-xs text-[var(--text-secondary)]">{{ __('Draft changes are not live until published. Session preview applies draft tokens on the public site for your browser only.') }}</p>
            </x-admin.card>
        @endif
    @endif
</div>

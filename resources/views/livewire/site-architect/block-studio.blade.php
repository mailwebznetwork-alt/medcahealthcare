<div class="space-y-6">
    @if ($statusMessage)<p class="text-sm text-[var(--success)]" role="status">{{ $statusMessage }}</p>@endif
    @if ($errorMessage)<p class="text-sm text-[var(--danger)]" role="alert">{{ $errorMessage }}</p>@endif

    <x-admin.card :title="__('Block deployment studio')">
        <p class="mom-body-text mb-4 text-sm text-[var(--text-secondary)]">{{ __('Media mapping and section design controls are stored in blocks.settings_json. Pages can override per block via block_overrides_json.') }}</p>
        <label class="block max-w-md">
            <span class="mom-label">{{ __('Block') }}</span>
            <select wire:model.live="block_slug" class="mom-input w-full">
                @foreach ($blocks as $block)
                    <option value="{{ $block->block_slug }}">{{ $block->block_name }} ({{ $block->block_slug }})</option>
                @endforeach
            </select>
        </label>
    </x-admin.card>

    <div class="flex flex-wrap gap-2">
        @foreach (['media' => __('Media mapping'), 'section' => __('Section controls'), 'style' => __('Style variant')] as $key => $label)
            <button type="button" wire:click="$set('activePanel', '{{ $key }}')" @class(['mom-cta-compact', 'mom-cta-primary' => $activePanel === $key, 'mom-cta-ghost' => $activePanel !== $key])>{{ $label }}</button>
        @endforeach
    </div>

    <x-admin.card>
        @if ($activePanel === 'media')
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($mediaSlots as $slot)
                    <div class="rounded-lg border border-[var(--border-panel-soft)] p-3">
                        <span class="mom-label">{{ str_replace('_', ' ', ucfirst($slot)) }}</span>
                        @if (! empty($media[$slot]))
                            <p class="mt-1 truncate text-xs text-[var(--text-secondary)]">{{ $media[$slot] }}</p>
                        @endif
                        <input type="file" wire:model="uploads.{{ $slot }}" class="mom-input mt-2 w-full text-xs" />
                        <input type="text" wire:model="media.{{ $slot }}" placeholder="{{ __('Path or URL') }}" class="mom-input mt-2 w-full text-xs" />
                        <button type="button" wire:click="removeMedia('{{ $slot }}')" class="mom-cta-compact mom-cta-ghost mt-2 text-xs">{{ __('Remove') }}</button>
                    </div>
                @endforeach
            </div>
        @elseif ($activePanel === 'section')
            <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($sectionKeys as $key)
                    @if (str_starts_with($key, 'visibility_'))
                        <label class="flex items-center gap-2 rounded border border-[var(--border-panel-soft)] px-3 py-2">
                            <input type="checkbox" wire:model="section.{{ $key }}" class="rounded" />
                            <span class="text-sm">{{ str_replace('_', ' ', ucfirst($key)) }}</span>
                        </label>
                    @else
                        <label class="block">
                            <span class="mom-label text-xs">{{ str_replace('_', ' ', ucfirst($key)) }}</span>
                            <input type="text" wire:model="section.{{ $key }}" class="mom-input w-full text-sm" />
                        </label>
                    @endif
                @endforeach
            </div>
        @else
            <label class="block max-w-xs">
                <span class="mom-label">{{ __('Style variant') }}</span>
                <select wire:model="style_variant" class="mom-input w-full">
                    @foreach ($styleVariants as $variant)
                        <option value="{{ $variant }}">{{ $variant }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        <div class="mt-6 flex flex-wrap gap-2">
            <button type="button" wire:click="preview" class="mom-cta-compact mom-cta-ghost">{{ __('Preview') }}</button>
            <button type="button" wire:click="saveDraft" class="mom-cta-compact mom-cta-primary">{{ __('Save to block') }}</button>
        </div>
    </x-admin.card>

    @if ($preview_html)
        <x-admin.card :title="__('Preview')">
            <div class="rounded-lg border border-[var(--border-panel-soft)] bg-white p-4 text-slate-900">{!! $preview_html !!}</div>
        </x-admin.card>
    @endif
</div>

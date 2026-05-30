@props([
    'moduleOptions' => [],
    'wireModel' => 'module_choice',
    'appendAction' => 'appendModuleToken',
])

<div>
    <label class="block text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">{{ __('Insert custom module…') }}</label>
    <div class="mt-2 flex flex-wrap items-center gap-2">
        <select wire:model.live="{{ $wireModel }}" class="min-w-[14rem] flex-1 rounded-mom-chrome border border-[var(--border-panel-soft)] bg-[var(--bg-card-matte)] px-3 py-2 text-sm text-[var(--text-primary)]">
            <option value="">{{ __('— Choose a module —') }}</option>
            @foreach ($moduleOptions as $option)
                <option value="{{ $option['key'] }}">
                    {{ $option['label'] }}
                    @if ($option['source'] === 'dynamic')
                        ({{ __('Custom') }})
                    @endif
                </option>
            @endforeach
        </select>
        <button type="button" wire:click="{{ $appendAction }}" wire:loading.attr="disabled" class="rounded-mom-chrome border border-[var(--border-panel-soft)] px-3 py-2 text-sm text-[var(--text-primary)] hover:bg-[var(--bg-hover)] disabled:opacity-50">{{ __('Add module token') }}</button>
        <a href="{{ route('site-architect.modules.index') }}" class="text-xs text-[var(--text-muted)] underline underline-offset-2 hover:text-[var(--text-primary)]" target="_blank" rel="noopener">{{ __('Module Builder →') }}</a>
    </div>
    <p class="mom-subtext mt-2">{{ __('Inserts a module token such as double-braced module:products. Custom modules from Module Builder appear here automatically.') }}</p>
    @error($wireModel) <span class="mt-2 block text-xs text-[var(--danger)]">{{ $message }}</span> @enderror
</div>

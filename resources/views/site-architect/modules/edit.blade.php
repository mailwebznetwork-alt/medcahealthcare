<x-site-architect.workspace :page-title="__('Edit module')" :welcome-line="__('Update module metadata and append new fields. Existing columns are preserved.')">
    <h2 class="mom-section-title mb-8">{{ __('Edit module') }}: {{ $module->name }}</h2>

    @if (session('status') === 'module-updated')
        <p class="mom-body-text mb-6 text-[var(--success)]" role="status">{{ __('Module updated.') }}</p>
    @endif

    @php
        $oldFields = old('fields');
        $seedFields = $oldFields ?? $module->fieldDefinitions->map(fn ($f) => [
            'id' => $f->id,
            'label' => $f->label,
            'field_name' => $f->field_name,
            'field_type' => $f->field_type,
            'is_required' => $f->is_required,
            'options' => implode(', ', $f->selectOptions()),
        ])->values()->all();

        if ($seedFields === []) {
            $seedFields = [];
        }
    @endphp

    <x-module-field-builder.form :action="route('site-architect.modules.update', $module)" method="PUT" :fields="$seedFields">
        <x-admin.card>
            <h3 class="mom-section-title">{{ __('Module details') }}</h3>
            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <x-input-label for="name" :value="__('Module name')" variant="mom" />
                    <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $module->name)" required variant="mom" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" variant="mom" />
                </div>
                <div>
                    <x-input-label :value="__('Slug / table')" variant="mom" />
                    <p class="mt-2 font-mono text-sm text-[var(--text-secondary)]">{{ $module->slug }} → {{ $module->table_name }}</p>
                </div>
                <div class="flex items-center gap-3 md:col-span-2">
                    <input type="hidden" name="is_active" value="0" />
                    <input id="is_active" name="is_active" type="checkbox" value="1" class="h-4 w-4 rounded border-[rgba(255,255,255,0.12)] bg-[rgba(28,22,18,0.75)] text-mom-gold focus:ring-1 focus:ring-[rgba(197,160,89,0.35)]" @checked(old('is_active', $module->is_active)) />
                    <x-input-label for="is_active" :value="__('Module active (records CRUD enabled)')" variant="mom" />
                </div>
            </div>
        </x-admin.card>

        @include('site-architect.modules.partials.field-builder', ['fieldTypes' => $fieldTypes, 'seedFields' => $seedFields])

        <div class="flex flex-wrap gap-3">
            <x-primary-button variant="mom">{{ __('Save module') }}</x-primary-button>
            <a href="{{ route('site-architect.modules.records.index', $module) }}" class="mom-cta-ghost">{{ __('View records') }}</a>
            <a href="{{ route('site-architect.modules.index') }}" class="mom-cta-ghost">{{ __('Back') }}</a>
        </div>
    </x-module-field-builder.form>

    @can('delete', $module)
        <form method="post" action="{{ route('site-architect.modules.destroy', $module) }}" class="mt-10" onsubmit="return confirm(@js(__('Delete this module and drop its table? This cannot be undone.')))">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm font-semibold text-[var(--danger)] hover:underline">{{ __('Delete module') }}</button>
        </form>
    @endcan
</x-site-architect.workspace>

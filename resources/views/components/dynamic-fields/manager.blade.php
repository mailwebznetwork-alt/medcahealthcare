@props([
    'module',
    'fieldTypes',
])

@php
    /** @var \App\Models\Module $module */
    $seedFields = $module->fieldDefinitions->map(fn ($f) => [
        'id' => $f->id,
        'label' => $f->label,
        'field_name' => $f->field_name,
        'field_type' => $f->field_type,
        'is_required' => $f->is_required,
        'options' => implode(', ', $f->selectOptions()),
    ])->values()->all();

    if ($seedFields === []) {
        $seedFields = [
            ['label' => '', 'field_name' => '', 'field_type' => 'text', 'is_required' => false, 'options' => ''],
        ];
    }
@endphp

@can('update', $module)
    <section class="mom-card mb-8 p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h3 class="mom-section-title">{{ __('Manage custom fields') }}</h3>
                <p class="mom-subtext mt-2 max-w-2xl">
                    {{ __('Define extra fields for :module. Values are stored in the :column column unless column storage is enabled for this module.', [
                        'module' => $module->name,
                        'column' => $module->customFieldsColumn(),
                    ]) }}
                </p>
            </div>
            <span class="rounded-full bg-[rgba(197,160,89,0.12)] px-3 py-1 text-xs font-semibold text-mom-gold">{{ __('Module Builder') }}</span>
        </div>

        @if (session('status') === 'legacy-module-fields-updated')
            <p class="mom-body-text mt-4 text-[var(--success)]" role="status">{{ __('Custom fields updated.') }}</p>
        @endif

        <x-module-field-builder.form
            :action="route('operations.managed-modules.fields.update', $module)"
            method="PUT"
            :fields="$seedFields"
            class="mt-6"
        >
            @include('site-architect.modules.partials.field-builder', ['fieldTypes' => $fieldTypes])

            <div class="flex flex-wrap gap-3">
                <x-primary-button variant="mom">{{ __('Save custom fields') }}</x-primary-button>
            </div>
        </x-module-field-builder.form>
    </section>
@endcan

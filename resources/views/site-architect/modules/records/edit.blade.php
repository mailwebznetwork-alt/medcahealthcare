@php
    /** @var \App\Models\Module $module */
    /** @var object $record */
@endphp

<x-site-architect.workspace :page-title="__('Edit record')" :welcome-line="$module->name">
    <h2 class="mom-section-title mb-8">{{ __('Edit :module record #:id', ['module' => $module->name, 'id' => $record->id]) }}</h2>

    <x-dynamic-fields.record-shell
        :module="$module"
        :record="$record"
        :action="route('site-architect.modules.records.update', [$module, $record->id])"
        method="PUT"
        :submit-label="__('Update record')"
    />
</x-site-architect.workspace>

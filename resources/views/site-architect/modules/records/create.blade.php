<x-site-architect.workspace :page-title="__('Add record')" :welcome-line="$module->name">
    <h2 class="mom-section-title mb-8">{{ __('Add :module record', ['module' => $module->name]) }}</h2>

    <x-dynamic-fields.record-shell
        :module="$module"
        :record="$record"
        :action="route('site-architect.modules.records.store', $module)"
        :submit-label="__('Save record')"
    />
</x-site-architect.workspace>

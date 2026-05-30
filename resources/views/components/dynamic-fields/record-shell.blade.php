@props([
    'module',
    'record',
    'action',
    'method' => 'POST',
    'submitLabel',
])

@php
    /** @var \App\Models\Module $module */
    $submitLabel = $submitLabel ?? __('Save record');
@endphp

<form method="post" action="{{ $action }}" class="space-y-8">
    @csrf
    @if (! in_array(strtoupper($method), ['GET', 'POST'], true))
        @method($method)
    @endif

    <x-dynamic-fields.record-form :module="$module" :record="$record" />

    @if ($module->fieldDefinitions->isNotEmpty())
        <div class="flex flex-wrap gap-3">
            <x-primary-button variant="mom">{{ $submitLabel }}</x-primary-button>
            <a href="{{ route('site-architect.modules.records.index', $module) }}" class="mom-cta-ghost">{{ __('Cancel') }}</a>
        </div>
    @endif
</form>

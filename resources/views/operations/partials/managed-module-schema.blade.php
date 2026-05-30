@php
    /** @var \App\Models\Module|null $managedModule */
    /** @var object|null $customFieldValues */
    $fieldTypes = \App\Models\FieldDefinition::typeLabels();
@endphp

@if ($managedModule instanceof \App\Models\Module)
    <x-dynamic-fields.manager :module="$managedModule" :field-types="$fieldTypes" />
@endif

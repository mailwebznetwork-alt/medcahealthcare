@props([
    'module',
    'record',
])

@php
    /** @var \App\Models\Module $module */
    /** @var object $record */
    $module->loadMissing('fieldDefinitions');
@endphp

<div class="mom-card p-6">
    @if ($module->fieldDefinitions->isEmpty())
        <p class="mom-subtext">{{ __('No fields are defined for this module yet. Add fields under Module Builder → Edit schema, then return here to create records.') }}</p>
    @else
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @foreach ($module->fieldDefinitions as $field)
                @php
                    $inputName = 'fields['.$field->field_name.']';
                    $errorKey = 'fields.'.$field->field_name;
                    $value = old('fields.'.$field->field_name, $record->{$field->field_name} ?? null);
                    $requiredAttr = $field->is_required ? 'required' : '';
                    $inputId = 'field_'.$field->field_name;
                @endphp
                <div @class(['md:col-span-2' => $field->field_type === \App\Models\FieldDefinition::TYPE_TEXTAREA])>
                    <x-input-label :for="$inputId" :value="$field->label" variant="mom" />
                    @if ($field->field_type === \App\Models\FieldDefinition::TYPE_TEXTAREA)
                        <textarea id="{{ $inputId }}" name="{{ $inputName }}" rows="4" class="mt-2 block w-full rounded-mom-chrome border-[rgba(255,255,255,0.045)] bg-[rgba(28,22,18,0.75)] px-3 py-2.5 text-sm text-[var(--text-primary)] shadow-mom-inner" {{ $requiredAttr }}>{{ $value }}</textarea>
                    @elseif ($field->field_type === \App\Models\FieldDefinition::TYPE_BOOLEAN)
                        <div class="mt-2 flex items-center gap-3">
                            <input type="hidden" name="{{ $inputName }}" value="0">
                            <input id="{{ $inputId }}" name="{{ $inputName }}" type="checkbox" value="1" class="h-4 w-4 rounded border-[rgba(255,255,255,0.12)] bg-[rgba(28,22,18,0.75)] text-mom-gold focus:ring-1 focus:ring-[rgba(197,160,89,0.35)]" @checked(filter_var($value, FILTER_VALIDATE_BOOLEAN)) />
                            <span class="text-sm text-[var(--text-secondary)]">{{ __('Enabled') }}</span>
                        </div>
                    @elseif ($field->field_type === \App\Models\FieldDefinition::TYPE_SELECT)
                        <select id="{{ $inputId }}" name="{{ $inputName }}" class="mt-2 block w-full rounded-mom-chrome border-[rgba(255,255,255,0.045)] bg-[rgba(28,22,18,0.75)] px-3 py-2.5 text-sm text-[var(--text-primary)] shadow-mom-inner" {{ $requiredAttr }}>
                            <option value="">{{ __('Select…') }}</option>
                            @foreach ($field->selectOptions() as $option)
                                <option value="{{ $option }}" @selected((string) $value === (string) $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif ($field->field_type === \App\Models\FieldDefinition::TYPE_NUMBER)
                        <input id="{{ $inputId }}" name="{{ $inputName }}" type="number" step="any" class="mt-2 block w-full rounded-mom-chrome border-[rgba(255,255,255,0.045)] bg-[rgba(28,22,18,0.75)] px-3 py-2.5 text-sm text-[var(--text-primary)] shadow-mom-inner" value="{{ $value }}" {{ $requiredAttr }} />
                    @elseif ($field->field_type === \App\Models\FieldDefinition::TYPE_DATE)
                        <input id="{{ $inputId }}" name="{{ $inputName }}" type="date" class="mt-2 block w-full rounded-mom-chrome border-[rgba(255,255,255,0.045)] bg-[rgba(28,22,18,0.75)] px-3 py-2.5 text-sm text-[var(--text-primary)] shadow-mom-inner" value="{{ $value }}" {{ $requiredAttr }} />
                    @elseif ($field->field_type === \App\Models\FieldDefinition::TYPE_EMAIL)
                        <input id="{{ $inputId }}" name="{{ $inputName }}" type="email" class="mt-2 block w-full rounded-mom-chrome border-[rgba(255,255,255,0.045)] bg-[rgba(28,22,18,0.75)] px-3 py-2.5 text-sm text-[var(--text-primary)] shadow-mom-inner" value="{{ $value }}" {{ $requiredAttr }} />
                    @elseif ($field->field_type === \App\Models\FieldDefinition::TYPE_URL)
                        <input id="{{ $inputId }}" name="{{ $inputName }}" type="url" class="mt-2 block w-full rounded-mom-chrome border-[rgba(255,255,255,0.045)] bg-[rgba(28,22,18,0.75)] px-3 py-2.5 text-sm text-[var(--text-primary)] shadow-mom-inner" value="{{ $value }}" {{ $requiredAttr }} />
                    @else
                        <input id="{{ $inputId }}" name="{{ $inputName }}" type="text" class="mt-2 block w-full rounded-mom-chrome border-[rgba(255,255,255,0.045)] bg-[rgba(28,22,18,0.75)] px-3 py-2.5 text-sm text-[var(--text-primary)] shadow-mom-inner" value="{{ $value }}" {{ $requiredAttr }} />
                    @endif
                    <x-input-error class="mt-2" :messages="$errors->get($errorKey)" variant="mom" />
                </div>
            @endforeach
        </div>
    @endif
</div>

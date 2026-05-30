@php
    /** @var \App\Models\Module $module */
    /** @var \Illuminate\Support\Collection<int, object> $records */
@endphp

<section class="medca-dynamic-module my-10" data-module="{{ $module->slug }}">
    <div class="mx-auto max-w-6xl px-4 md:px-6">
        @if ($records->isNotEmpty())
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($records as $record)
                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        @foreach ($module->fieldDefinitions as $field)
                            @php $value = $record->{$field->field_name} ?? null; @endphp
                            @if (filled($value))
                                <div @class(['mb-3' => ! $loop->last])>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $field->label }}</p>
                                    @if ($field->field_type === \App\Models\FieldDefinition::TYPE_TEXTAREA)
                                        <div class="mt-1 text-sm text-slate-700">{!! nl2br(e((string) $value)) !!}</div>
                                    @elseif ($field->field_type === \App\Models\FieldDefinition::TYPE_BOOLEAN)
                                        <p class="mt-1 text-sm text-slate-800">{{ filter_var($value, FILTER_VALIDATE_BOOLEAN) ? __('Yes') : __('No') }}</p>
                                    @else
                                        <p class="mt-1 text-sm text-slate-800">{{ $value }}</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

@props([
    'action',
    'fields',
    'method' => 'POST',
])

<form
    method="post"
    action="{{ $action }}"
    {{ $attributes->class(['space-y-8']) }}
    x-data="{
        fields: @js($fields),
        addField() {
            this.fields.push({ label: '', field_name: '', field_type: 'text', is_required: false, options: '' });
        },
        removeField(index) {
            this.fields.splice(index, 1);
        },
        syncFieldName(index) {
            const label = this.fields[index].label || '';
            const prev = this.fields[index]._prevLabel || '';
            if (! this.fields[index].field_name || this.fields[index].field_name === this.slugify(prev)) {
                this.fields[index].field_name = this.slugify(label);
            }
            this.fields[index]._prevLabel = label;
        },
        slugify(value) {
            return String(value || '').toLowerCase().trim()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '')
                .replace(/^(\d)/, 'f_$1');
        },
    }"
>
    @csrf
    @if (! in_array(strtoupper($method), ['GET', 'POST'], true))
        @method($method)
    @endif

    {{ $slot }}
</form>

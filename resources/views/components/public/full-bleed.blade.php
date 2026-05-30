@props([
    'tag' => 'section',
])

<{{ $tag }} {{ $attributes->class(['medca-full-bleed w-full']) }}>
    {{ $slot }}
</{{ $tag }}>

@props([
    'tag' => 'div',
    'inset' => false,
])

@php
    $shell = $inset
        ? 'mx-auto w-full max-w-6xl'
        : 'mx-auto w-full max-w-6xl px-4 md:px-6 lg:px-8';
@endphp

<{{ $tag }} {{ $attributes->class([$shell]) }}>
    {{ $slot }}
</{{ $tag }}>

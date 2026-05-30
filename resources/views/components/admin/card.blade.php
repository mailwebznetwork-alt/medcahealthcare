@props([
    'tag' => 'div',
    'interactive' => false,
    'flush' => false,
    'padding' => 'p-6',
])

<{{ $tag }}
    {{ $attributes->class([
        'mom-card',
        'mom-card-interactive' => $interactive,
        $padding => ! $flush,
        'p-0' => $flush && $padding === 'p-6',
    ]) }}
>
    {{ $slot }}
</{{ $tag }}>

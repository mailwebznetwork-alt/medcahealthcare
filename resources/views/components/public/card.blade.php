@props([
    'interactive' => false,
    'padding' => 'p-8',
])

<div
    {{ $attributes->class([
        'service-card',
        $padding,
    ]) }}
>
    {{ $slot }}
</div>

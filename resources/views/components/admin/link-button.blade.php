@props([
    'variant' => 'primary',
    'size' => 'default',
    'active' => false,
])

@php
    $classes = match ($variant) {
        'ghost' => 'mom-cta-ghost',
        default => 'mom-cta-primary',
    };

    if ($active && $variant === 'ghost') {
        $classes .= ' mom-cta-ghost--active';
    }

    if ($size === 'compact') {
        $classes .= ' mom-cta-compact';
    }
@endphp

<a {{ $attributes->class([$classes, 'no-underline']) }}>
    {{ $slot }}
</a>

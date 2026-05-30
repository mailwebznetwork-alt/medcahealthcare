@props([
    'tag' => 'section',
])

<x-public.full-bleed {{ $attributes->class(['py-10 md:py-12']) }}>
    <x-public.content-shell>
        {{ $slot }}
    </x-public.content-shell>
</x-public.full-bleed>

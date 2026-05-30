@props([
    'tag' => 'section',
])

<x-public.full-bleed {{ $attributes->class(['py-12 md:py-16']) }}>
    <x-public.content-shell>
        {{ $slot }}
    </x-public.content-shell>
</x-public.full-bleed>

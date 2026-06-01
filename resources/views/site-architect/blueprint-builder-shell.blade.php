<x-site-architect.workspace
    :page-title="__('Blueprint Builder')"
    :welcome-line="__('Deployment Engine — generate Pages and block order from industry blueprints without a second CMS.')"
>
    @include('site-architect.partials.deployment-hub')
    @livewire('site-architect.blueprint-builder')
</x-site-architect.workspace>

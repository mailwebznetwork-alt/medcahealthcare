<x-layouts.markonminds
    page-title="{{ __('Marketing Intelligence Platform') }}"
    :welcome-line="__('Attribution, conversion tracking, WhatsApp/call analytics, and executive reporting.')"
>
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('modules.marketing') }}" class="mom-cta-compact mom-cta-ghost">{{ __('Campaign dashboard') }}</a>
        <a href="{{ route('modules.marketing.intelligence') }}" class="mom-cta-compact mom-cta-primary">{{ __('Intelligence platform') }}</a>
    </div>
    @livewire('marketing.intelligence-dashboard')
</x-layouts.markonminds>

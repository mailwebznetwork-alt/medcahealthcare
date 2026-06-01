<x-layouts.markonminds
    page-title="{{ __('Marketing Intelligence') }}"
    :welcome-line="__('Campaigns, ads, communication, and insights — GA4 detail reports live under Growth Center → GA4.')"
>
    <div class="mb-6">
        <a href="{{ route('modules.marketing.intelligence') }}" class="mom-cta-compact mom-cta-primary">{{ __('Open intelligence platform') }}</a>
    </div>
    @livewire('marketing.dashboard')
</x-layouts.markonminds>

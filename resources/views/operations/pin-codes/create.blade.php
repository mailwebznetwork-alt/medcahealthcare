<x-app-layout
    :page-title="__('Add pin code')"
    :welcome-line="__('Register a service area with operational and SEO metadata.')"
>
    <form method="post" action="{{ route('operations.pin-codes.store') }}" class="space-y-8">
        @csrf
        @include('operations.pin-codes._form')
        <div class="flex flex-wrap gap-3">
            <x-primary-button variant="mom">{{ __('Save pin code') }}</x-primary-button>
            <a href="{{ route('operations.pin-codes.index') }}" class="inline-flex items-center justify-center rounded-mom-md border border-[rgba(255,255,255,0.045)] bg-[rgba(255,255,255,0.03)] px-5 py-2.5 text-xs font-semibold uppercase tracking-widest text-[var(--text-secondary)] shadow-mom-inner transition-all duration-320 ease-premium hover:border-[rgba(212,169,95,0.16)] hover:text-[var(--text-primary)]">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-app-layout>

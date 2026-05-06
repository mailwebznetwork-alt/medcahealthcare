{{-- Public Medca header: desktop + mobile drawer aligned with medca-healthcare UI. --}}
@php
    $isSuperAdmin = auth()->check() && strtolower((string) auth()->user()?->role) === 'super_admin';
    $phoneTel = preg_replace('/\D+/', '', (string) config('medca.phone_tel'));
    $whatsAppUrl = (string) config('medca.whatsapp_url');
    $homeUrl = url('/');
    $aboutUrl = url('/#about');
    $servicesUrl = url('/#services');
    $locationsUrl = url('/#locations');
    $careersUrl = route('careers.index');
    $contactUrl = url('/#contact');
@endphp

<header class="relative z-[999] w-full bg-white shadow-sm">
    <div class="bg-[#002366] px-4 py-2 text-[11px] text-white sm:px-6">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-2">
            <span class="font-medium">{{ config('medca.top_bar_claim') }}</span>
            <span class="inline-flex items-center gap-1.5 text-slate-100">
                <svg class="h-3.5 w-3.5 shrink-0 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>{{ config('medca.location_display') }}</span>
            </span>
        </div>
    </div>

    <div class="border-b border-[#eeeeee]">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4" x-data="{ open: false }">
            <a
                href="{{ url('/') }}"
                class="inline-flex min-w-0 shrink-0 items-center gap-3 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#6f42c1]/40"
                aria-label="{{ config('medca.brand_name') }} — {{ __('Home') }}"
            >
                <img
                    src="{{ asset('images/medca-logo.png') }}"
                    alt="{{ config('medca.brand_name') }}"
                    width="200"
                    height="56"
                    class="h-9 w-auto max-w-[min(11rem,50vw)] object-contain"
                    loading="eager"
                    decoding="async"
                />
                <span class="hidden min-w-0 sm:block">
                    <span class="block truncate text-base font-semibold text-[#002366]">{{ config('medca.brand_name') }}</span>
                    <span class="block truncate text-[10px] font-semibold uppercase tracking-[0.28em] text-[#4a6fa8]">{{ mb_strtoupper(config('medca.tagline')) }}</span>
                </span>
            </a>

            <nav class="hidden items-center gap-5 lg:flex" aria-label="{{ __('Primary') }}">
                <a href="{{ $homeUrl }}" class="rounded-xl border border-transparent px-3 py-2 text-[11px] font-semibold uppercase tracking-widest text-slate-600 transition hover:border-slate-200">{{ __('Home') }}</a>
                <a href="{{ $aboutUrl }}" class="rounded-xl border border-transparent px-3 py-2 text-[11px] font-semibold uppercase tracking-widest text-slate-600 transition hover:border-slate-200">{{ __('About') }}</a>
                <a href="{{ $servicesUrl }}" class="rounded-xl border border-transparent px-3 py-2 text-[11px] font-semibold uppercase tracking-widest text-slate-600 transition hover:border-slate-200">{{ __('Services') }}</a>
                <a href="{{ $locationsUrl }}" class="rounded-xl border border-transparent px-3 py-2 text-[11px] font-semibold uppercase tracking-widest text-slate-600 transition hover:border-slate-200">{{ __('Locations') }}</a>
                <a href="{{ $careersUrl }}" class="rounded-xl border border-transparent px-3 py-2 text-[11px] font-semibold uppercase tracking-widest text-slate-600 transition hover:border-slate-200">{{ __('Careers') }}</a>
                <a href="{{ $contactUrl }}" class="rounded-xl border border-transparent px-3 py-2 text-[11px] font-semibold uppercase tracking-widest text-slate-600 transition hover:border-slate-200">{{ __('Contact') }}</a>
            </nav>

            <div class="flex items-center space-x-2">
                <a
                    href="{{ url('/#callback') }}"
                    class="rounded bg-[#83b735] px-4 py-3 text-xs font-bold text-white shadow-sm transition hover:brightness-105 lg:hidden"
                >
                    {{ __('Book Callback') }}
                </a>

                <button
                    type="button"
                    @click="open = true"
                    class="rounded p-1 text-slate-700 lg:hidden"
                    :aria-expanded="open"
                    aria-label="{{ __('Open navigation') }}"
                >
                    <span class="sr-only">{{ __('Open navigation') }}</span>
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <template x-teleport="body">
                <div
                    x-show="open"
                    x-cloak
                    x-transition.opacity
                    class="fixed inset-0 z-[99990] lg:hidden"
                    @keydown.escape.window="open = false"
                >
                    <div class="absolute inset-0 bg-slate-900/45 backdrop-blur-sm" @click="open = false" aria-hidden="true"></div>

                    <aside
                        class="absolute inset-y-0 right-0 flex h-full w-[76%] max-w-sm transform flex-col border-l border-slate-200 bg-white shadow-2xl transition-transform duration-300 ease-in-out"
                        :class="open ? 'translate-x-0' : 'translate-x-full'"
                        @click.stop
                    >
                        <div class="flex items-center justify-between border-b border-slate-200 bg-white px-5 py-5">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-clinical-100 bg-clinical-50 text-clinical-700">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold tracking-wide text-clinical-900">{{ __('Medca Navigation') }}</p>
                                    <p class="mt-1 text-[11px] uppercase tracking-[0.28em] text-slate-500">{{ __('Strategic Commander') }}</p>
                                </div>
                            </div>
                            <button type="button" @click="open = false" class="rounded-2xl border border-slate-200 bg-slate-50 p-2 text-slate-700" aria-label="{{ __('Close navigation') }}">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="border-b border-slate-200 px-5 py-4">
                            <div class="flex gap-2">
                                <input type="text" value="" placeholder="{{ __('Search services...') }}" class="h-12 w-full rounded-2xl border border-[#d5dce8] bg-white px-4 text-sm text-slate-600 placeholder:text-[#8ea0c4] focus:border-[#2f5fd0] focus:outline-none" />
                                <button type="button" class="h-12 rounded-2xl bg-[#0c4fbd] px-5 text-xs font-bold uppercase tracking-wider text-white">{{ __('Go') }}</button>
                            </div>
                        </div>

                        <nav class="custom-scrollbar flex-1 overflow-y-auto px-5 py-4">
                            <a href="{{ $homeUrl }}" @click="open = false" class="flex min-h-[60px] items-center border-b border-slate-100 px-1 text-sm font-semibold uppercase tracking-wide text-slate-800 transition hover:bg-slate-50">{{ __('Home') }}</a>
                            <a href="{{ $aboutUrl }}" @click="open = false" class="flex min-h-[60px] items-center border-b border-slate-100 px-1 text-sm font-semibold uppercase tracking-wide text-slate-800 transition hover:bg-slate-50">{{ __('About Us') }}</a>
                            <a href="{{ $servicesUrl }}" @click="open = false" class="flex min-h-[60px] items-center border-b border-slate-100 px-1 text-sm font-semibold uppercase tracking-wide text-slate-800 transition hover:bg-slate-50">{{ __('Services') }}</a>
                            <a href="{{ $locationsUrl }}" @click="open = false" class="flex min-h-[60px] items-center border-b border-slate-100 px-1 text-sm font-semibold uppercase tracking-wide text-slate-800 transition hover:bg-slate-50">{{ __('Locations') }}</a>
                            <a href="{{ $careersUrl }}" @click="open = false" class="flex min-h-[60px] items-center border-b border-slate-100 px-1 text-sm font-semibold uppercase tracking-wide text-slate-800 transition hover:bg-slate-50">{{ __('Careers') }}</a>
                            <a href="{{ $contactUrl }}" @click="open = false" class="flex min-h-[60px] items-center border-b border-slate-100 px-1 text-sm font-semibold uppercase tracking-wide text-slate-800 transition hover:bg-slate-50">{{ __('Contact Us') }}</a>

                            @if($isSuperAdmin)
                                <button type="button" class="mt-5 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-slate-800 shadow-sm transition hover:bg-slate-50">{{ __('Enable Edit Mode') }}</button>
                                <button type="button" class="mt-3 block w-full rounded-2xl border border-clinical-600 bg-clinical-600 px-4 py-3 text-left text-xs font-semibold uppercase tracking-widest text-white shadow-md transition hover:bg-clinical-700">{{ __('Publish Changes') }}</button>
                                <button type="button" class="mt-4 block w-full rounded-2xl border border-clinical-200 bg-clinical-50 px-4 py-3 text-left text-sm font-semibold uppercase tracking-widest text-clinical-800 transition hover:bg-clinical-100">{{ __('Super Admin Console') }}</button>
                            @endif
                        </nav>

                        <div class="border-t border-slate-200 bg-slate-50 p-4">
                            <div class="grid grid-cols-2 gap-2">
                                <a href="tel:{{ $phoneTel }}" class="flex min-h-[52px] items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-[#123f9d] shadow-sm">{{ __('Call Now') }}</a>
                                <a href="{{ $whatsAppUrl }}" target="_blank" rel="noopener noreferrer" class="flex min-h-[52px] items-center justify-center rounded-xl border border-emerald-700 bg-emerald-700 px-3 text-sm font-bold text-white">{{ __('WhatsApp') }}</a>
                            </div>
                        </div>
                    </aside>
                </div>
            </template>
        </div>
    </div>
</header>

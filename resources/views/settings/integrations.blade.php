<x-app-layout
    :page-title="__('Settings')"
    :welcome-line="__('Integrations workspace for platform and channel connections.')"
>
    <section class="mom-card p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="mom-section-title">{{ __('Integrations') }}</h2>
                <p class="mom-body-text mt-2 text-[var(--text-secondary)]">
                    {{ __('Manage providers, secure credentials, and connection state for core services.') }}
                </p>
            </div>
            <span class="mom-micro text-mom-gold">{{ __('Total: :count', ['count' => $integrations->count()]) }}</span>
        </div>
    </section>

    <section class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($integrations as $integration)
            <article class="mom-card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="mom-micro">{{ str_replace('_', ' ', $integration->type) }}</p>
                        <h3 class="mt-2 text-base font-semibold text-[var(--text-primary)]">{{ str_replace('_', ' ', $integration->name) }}</h3>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-mom-chrome border border-[var(--border-panel-soft)] px-3 py-1 text-xs {{ $integration->is_enabled ? 'text-[var(--success)]' : 'text-[var(--text-muted)]' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $integration->is_enabled ? 'bg-[var(--success)]' : 'bg-[var(--text-muted)]' }}"></span>
                        {{ $integration->is_enabled ? __('Enabled') : __('Disabled') }}
                    </span>
                </div>

                <dl class="mom-body-text mt-4 space-y-1 text-[var(--text-secondary)]">
                    <div class="flex justify-between gap-4">
                        <dt>{{ __('Last used') }}</dt>
                        <dd class="text-right text-[var(--text-primary)]">
                            {{ $integration->last_used_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?? __('Never') }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt>{{ __('Credential keys') }}</dt>
                        <dd class="text-right text-[var(--text-primary)]">{{ count($integration->credentials) }}</dd>
                    </div>
                </dl>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a
                        href="{{ route('admin.settings.integrations.show', $integration->name) }}"
                        target="_blank"
                        class="mom-cta-primary !px-3 !py-2 !text-[11px]"
                    >{{ __('View') }}</a>
                    <form method="post" action="{{ route('admin.settings.integrations.toggle', $integration->name) }}" class="inline-flex">
                        @csrf
                        @method('patch')
                        <button type="submit" class="mom-cta-ghost !px-3 !py-2 !text-[11px]">
                            {{ $integration->is_enabled ? __('Disable') : __('Enable') }}
                        </button>
                    </form>
                    <form method="post" action="{{ route('admin.settings.integrations.test', $integration->name) }}" class="inline-flex">
                        @csrf
                        <button type="submit" class="mom-cta-ghost !px-3 !py-2 !text-[11px]">{{ __('Test') }}</button>
                    </form>
                </div>
            </article>
        @empty
            <article class="mom-card p-6 md:col-span-2 xl:col-span-3">
                <p class="mom-body-text text-[var(--text-muted)]">{{ __('No integrations found. Run migrations and refresh this page.') }}</p>
            </article>
        @endforelse
    </section>
</x-app-layout>

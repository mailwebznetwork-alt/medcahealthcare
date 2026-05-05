<x-app-layout
    :page-title="__('Growth Center')"
    :welcome-line="__('Competitor War Room workspace.')"
>
    <section class="mom-card p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="mom-section-title">{{ __('Competitors') }}</h2>
                <p class="mom-body-text mt-2 text-[var(--text-secondary)]">
                    {{ __('Track competitors, keywords, and conversion movement in one place.') }}
                </p>
            </div>
            <span class="mom-micro text-mom-gold">{{ __('Total: :count', ['count' => $competitors->total()]) }}</span>
        </div>
    </section>

    <section class="mom-card mt-8 p-6">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[44rem] text-left text-[13px]">
                <thead class="bg-[var(--bg-card-table-head)] text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--text-muted)]">
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('Competitor') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Website') }}</th>
                        <th class="px-4 py-3 font-medium text-right">{{ __('Keywords') }}</th>
                        <th class="px-4 py-3 font-medium text-right">{{ __('Leads') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Intercept') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[rgba(255,255,255,0.045)] text-[var(--text-secondary)]">
                    @forelse ($competitors as $competitor)
                        <tr>
                            <td class="px-4 py-3 text-[var(--text-primary)]">{{ $competitor->name }}</td>
                            <td class="px-4 py-3">{{ $competitor->website ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((int) $competitor->keywords_count) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((int) $competitor->leads_count) }}</td>
                            <td class="px-4 py-3">{{ $competitor->is_active ? __('Active') : __('Inactive') }}</td>
                            <td class="px-4 py-3">{{ $competitor->is_intercept_target ? __('Yes') : __('No') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-[var(--text-muted)]">{{ __('No competitors available.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $competitors->links() }}
        </div>
    </section>
</x-app-layout>

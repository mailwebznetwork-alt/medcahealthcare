<div class="space-y-6">
    <div class="flex flex-wrap gap-2">
        @foreach (['executive' => __('Executive'), 'whatsapp' => __('WhatsApp'), 'calls' => __('Calls'), 'attribution' => __('Attribution'), 'conversions' => __('Conversions'), 'reporting' => __('Reporting')] as $key => $label)
            <button type="button" wire:click="setTab('{{ $key }}')" @class(['mom-cta-compact', 'mom-cta-primary' => $tab === $key, 'mom-cta-ghost' => $tab !== $key])>{{ $label }}</button>
        @endforeach
    </div>

    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
        <label class="block">
            <span class="mom-label">{{ __('From') }}</span>
            <input type="date" wire:model.live="dateFrom" class="mom-input w-full" />
        </label>
        <label class="block">
            <span class="mom-label">{{ __('To') }}</span>
            <input type="date" wire:model.live="dateTo" class="mom-input w-full" />
        </label>
        <label class="block md:col-span-2">
            <span class="mom-label">{{ __('Trend granularity') }}</span>
            <select wire:model.live="trendGranularity" class="mom-input w-full">
                <option value="daily">{{ __('Daily') }}</option>
                <option value="weekly">{{ __('Weekly') }}</option>
                <option value="monthly">{{ __('Monthly') }}</option>
                <option value="quarterly">{{ __('Quarterly') }}</option>
            </select>
        </label>
    </div>

    @if ($tab === 'executive')
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                __('Total Leads') => $executive['total_leads'],
                __('Qualified') => $executive['qualified_leads'],
                __('Converted') => $executive['converted_leads'],
                __('Conversion Rate') => $executive['conversion_rate'].'%',
            ] as $label => $value)
                <x-admin.card :title="$label">
                    <p class="text-2xl font-bold text-[var(--text-primary)]">{{ $value }}</p>
                </x-admin.card>
            @endforeach
        </div>
        <div class="grid gap-4 lg:grid-cols-2">
            <x-admin.card :title="__('Top sources')">
                <ul class="space-y-1 text-sm">
                    @forelse ($executive['top_sources'] as $source => $count)
                        <li class="flex justify-between"><span>{{ $source }}</span><span>{{ $count }}</span></li>
                    @empty
                        <li class="text-[var(--text-secondary)]">{{ __('No data yet') }}</li>
                    @endforelse
                </ul>
            </x-admin.card>
            <x-admin.card :title="__('Top campaigns')">
                <ul class="space-y-1 text-sm">
                    @forelse ($executive['top_campaigns'] as $campaign => $count)
                        <li class="flex justify-between"><span>{{ $campaign }}</span><span>{{ $count }}</span></li>
                    @empty
                        <li class="text-[var(--text-secondary)]">{{ __('No data yet') }}</li>
                    @endforelse
                </ul>
            </x-admin.card>
        </div>
        <x-admin.card :title="__('Lead trend')">
            <ul class="grid gap-1 text-xs md:grid-cols-2 lg:grid-cols-3">
                @foreach ($trend as $point)
                    <li class="flex justify-between rounded border border-[var(--border-panel-soft)] px-2 py-1"><span>{{ $point['date'] }}</span><span>{{ $point['total'] }}</span></li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif

    @if ($tab === 'whatsapp')
        <div class="grid gap-4 md:grid-cols-3">
            @foreach (['today', 'week', 'month'] as $period)
                <x-admin.card :title="ucfirst($period)">
                    <p class="text-2xl font-bold">{{ $whatsapp[$period] ?? 0 }}</p>
                </x-admin.card>
            @endforeach
        </div>
        <div class="grid gap-4 lg:grid-cols-2">
            <x-admin.card :title="__('Source breakdown')">
                <ul class="space-y-1 text-sm">
                    @foreach (($whatsapp['by_source'] ?? []) as $source => $count)
                        <li class="flex justify-between"><span>{{ $source ?: __('Direct') }}</span><span>{{ $count }}</span></li>
                    @endforeach
                </ul>
            </x-admin.card>
            <x-admin.card :title="__('Top pages')">
                <ul class="space-y-1 text-sm">
                    @foreach (($whatsapp['top_pages'] ?? []) as $page => $count)
                        <li class="flex justify-between"><span>{{ $page }}</span><span>{{ $count }}</span></li>
                    @endforeach
                </ul>
            </x-admin.card>
        </div>
    @endif

    @if ($tab === 'calls')
        <div class="grid gap-4 md:grid-cols-3">
            @foreach (['today', 'week', 'month'] as $period)
                <x-admin.card :title="__('Calls — :period', ['period' => ucfirst($period)])">
                    <p class="text-2xl font-bold">{{ $calls[$period] ?? 0 }}</p>
                </x-admin.card>
            @endforeach
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <x-admin.card :title="__('Mobile vs desktop (month)')">
                <p class="text-sm">{{ __('Mobile') }}: {{ $calls['mobile'] ?? 0 }}</p>
                <p class="text-sm">{{ __('Desktop') }}: {{ $calls['desktop'] ?? 0 }}</p>
            </x-admin.card>
            <x-admin.card :title="__('By source')">
                <ul class="space-y-1 text-sm">
                    @foreach (($calls['by_source'] ?? []) as $source => $count)
                        <li class="flex justify-between"><span>{{ $source ?: __('Direct') }}</span><span>{{ $count }}</span></li>
                    @endforeach
                </ul>
            </x-admin.card>
        </div>
    @endif

    @if ($tab === 'attribution')
        <div class="grid gap-4 lg:grid-cols-2">
            <x-admin.card :title="__('First-touch attribution')">
                <ul class="space-y-1 text-sm">
                    @foreach ($attribution['first_touch'] as $source => $count)
                        <li class="flex justify-between"><span>{{ $source }}</span><span>{{ $count }}</span></li>
                    @endforeach
                </ul>
            </x-admin.card>
            <x-admin.card :title="__('Last-touch attribution')">
                <ul class="space-y-1 text-sm">
                    @foreach ($attribution['last_touch'] as $source => $count)
                        <li class="flex justify-between"><span>{{ $source }}</span><span>{{ $count }}</span></li>
                    @endforeach
                </ul>
            </x-admin.card>
        </div>
        <x-admin.card :title="__('Google Business Profile')">
            <dl class="grid gap-2 text-sm md:grid-cols-2">
                @foreach ($gbp as $key => $value)
                    <div class="flex justify-between"><dt>{{ str_replace('_', ' ', ucfirst($key)) }}</dt><dd>{{ $value }}</dd></div>
                @endforeach
            </dl>
        </x-admin.card>
    @endif

    @if ($tab === 'conversions')
        <x-admin.card :title="__('Conversion metrics')">
            <dl class="grid gap-2 text-sm md:grid-cols-2">
                <div><dt>{{ __('Conversion rate') }}</dt><dd>{{ $conversions['conversion_rate'] }}%</dd></div>
                <div><dt>{{ __('Lead velocity / day') }}</dt><dd>{{ $conversions['lead_velocity_per_day'] }}</dd></div>
                <div><dt>{{ __('Avg time to conversion (hrs)') }}</dt><dd>{{ $conversions['avg_time_to_conversion_hours'] ?? '—' }}</dd></div>
            </dl>
            <h4 class="mom-label mt-4">{{ __('Stage funnel') }}</h4>
            <ul class="space-y-1 text-sm">
                @foreach (($conversions['stage_counts'] ?? []) as $type => $count)
                    <li class="flex justify-between"><span>{{ str_replace('_', ' ', ucfirst($type)) }}</span><span>{{ $count }}</span></li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif

    @if ($tab === 'reporting')
        <x-admin.card :title="__('Export leads (CSV)')">
            <p class="mom-body-text mb-4 text-sm text-[var(--text-secondary)]">{{ __('Uses the date filters above. Max :rows rows.', ['rows' => config('marketing_automation.reporting.max_export_rows')]) }}</p>
            <a
                href="{{ route('modules.marketing.reports.leads.export', ['from' => $dateFrom, 'to' => $dateTo]) }}"
                class="mom-cta-compact mom-cta-primary"
            >{{ __('Download CSV') }}</a>
        </x-admin.card>
    @endif
</div>

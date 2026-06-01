<?php

namespace App\Services\Marketing\Retention;

use App\Models\LeadActivity;
use App\Models\MarketingClickEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MarketingDataRetentionService
{
    public function purgeClickEvents(?Carbon $before = null): int
    {
        if (! Schema::hasTable('marketing_click_events')) {
            return 0;
        }

        $before = $before ?? now()->subDays(config('marketing_automation.retention.click_events_days', 365));

        if (config('marketing_automation.retention.archive_before_delete', true)) {
            $this->archiveTableChunk('marketing_click_events', $before);
        }

        return MarketingClickEvent::query()->where('occurred_at', '<', $before)->delete();
    }

    public function purgeActivities(?Carbon $before = null): int
    {
        if (! Schema::hasTable('lead_activities')) {
            return 0;
        }

        $before = $before ?? now()->subDays(config('marketing_automation.retention.activities_days', 730));

        return LeadActivity::query()->where('occurred_at', '<', $before)->delete();
    }

    private function archiveTableChunk(string $table, Carbon $before): void
    {
        $path = 'marketing-archives/'.$table.'-'.$before->format('Y-m-d').'.json';
        if (Storage::disk('local')->exists($path)) {
            return;
        }

        $rows = match ($table) {
            'marketing_click_events' => MarketingClickEvent::query()->where('occurred_at', '<', $before)->limit(500)->get()->toJson(),
            default => '[]',
        };

        Storage::disk('local')->put($path, $rows);
    }
}

<?php

namespace App\Services\Marketing\Reporting;

use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarketingReportExporter
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function exportCsv(array $filters = []): StreamedResponse
    {
        $rows = $this->queryLeads($filters);
        $filename = 'marketing-leads-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'UUID', 'Name', 'Phone', 'Email', 'Service', 'Source', 'Campaign',
                'UTM Source', 'UTM Medium', 'UTM Campaign', 'First Touch Source', 'Last Touch Source',
                'Pipeline Stage', 'Status', 'Created At',
            ]);

            foreach ($rows as $lead) {
                fputcsv($out, [
                    $lead->uuid,
                    $lead->name,
                    $lead->phone,
                    $lead->email,
                    $lead->service,
                    $lead->source instanceof \BackedEnum ? $lead->source->value : $lead->source,
                    $lead->campaign,
                    $lead->utm_source,
                    $lead->utm_medium,
                    $lead->utm_campaign,
                    $lead->first_touch_source,
                    $lead->last_touch_source,
                    $lead->pipeline_stage instanceof \BackedEnum ? $lead->pipeline_stage->value : $lead->pipeline_stage,
                    $lead->status instanceof \BackedEnum ? $lead->status->value : $lead->status,
                    $lead->created_at?->toDateTimeString(),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Lead>
     */
    private function queryLeads(array $filters): Collection
    {
        $query = Lead::query()->orderByDesc('created_at');
        $max = config('marketing_automation.reporting.max_export_rows', 10000);

        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['from'])->startOfDay());
        }
        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['to'])->endOfDay());
        }
        if (! empty($filters['campaign'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('campaign', $filters['campaign'])
                    ->orWhere('utm_campaign', $filters['campaign']);
            });
        }
        if (! empty($filters['source'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('source', $filters['source'])
                    ->orWhere('utm_source', $filters['source']);
            });
        }
        if (! empty($filters['service'])) {
            $query->where('service', 'like', '%'.$filters['service'].'%');
        }

        return $query->limit($max)->get();
    }
}

<?php

namespace App\Services\Marketing\Pipeline;

use App\Enums\LeadPipelineStage;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadPipelineStageHistory;
use App\Models\MarketingConversionEvent;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class LeadPipelineService
{
    public function initialize(Lead $lead): void
    {
        if (! Schema::hasColumn('leads', 'pipeline_stage')) {
            return;
        }

        if ($lead->pipeline_stage === null) {
            $stage = LeadPipelineStage::fromLegacyStatus($lead->status);
            $lead->forceFill([
                'pipeline_stage' => $stage,
                'pipeline_stage_changed_at' => now(),
            ])->saveQuietly();
        }

        $this->recordHistory($lead, null, ($lead->pipeline_stage instanceof LeadPipelineStage ? $lead->pipeline_stage->value : LeadPipelineStage::NewLead->value), null, __('Lead created'));
        $this->logActivity($lead, 'lead_created', __('Lead created'), null, null);
        $this->recordConversion($lead, 'lead_created');
    }

    public function moveStage(Lead $lead, LeadPipelineStage $toStage, ?User $user = null, ?string $note = null): void
    {
        $from = $lead->pipeline_stage instanceof LeadPipelineStage
            ? $lead->pipeline_stage->value
            : (is_string($lead->pipeline_stage) ? $lead->pipeline_stage : null);

        if ($from === $toStage->value) {
            return;
        }

        $lead->forceFill([
            'pipeline_stage' => $toStage,
            'pipeline_stage_changed_at' => now(),
            'status' => $toStage->toLegacyStatus(),
            'converted_at' => in_array($toStage, [LeadPipelineStage::Converted, LeadPipelineStage::ActiveClient], true)
                ? ($lead->converted_at ?? now())
                : $lead->converted_at,
        ])->save();

        $this->recordHistory($lead, $from, $toStage->value, $user?->id, $note);
        $this->logActivity($lead, 'stage_change', __('Moved to :stage', ['stage' => $toStage->label()]), $note, $user?->id);

        match ($toStage) {
            LeadPipelineStage::AssessmentScheduled => $this->recordConversion($lead, 'assessment_scheduled'),
            LeadPipelineStage::ProposalSent => $this->recordConversion($lead, 'proposal_sent'),
            LeadPipelineStage::Converted, LeadPipelineStage::ActiveClient => $this->recordConversion($lead, 'client_converted'),
            default => null,
        };
    }

    public function logNote(Lead $lead, string $note, ?int $userId = null): void
    {
        $this->logActivity($lead, 'note', __('Internal note added'), $note, $userId);
    }

    private function recordHistory(Lead $lead, ?string $from, string $to, ?int $userId, ?string $note): void
    {
        if (! Schema::hasTable('lead_pipeline_stage_histories')) {
            return;
        }

        LeadPipelineStageHistory::query()->create([
            'lead_id' => $lead->id,
            'from_stage' => $from,
            'to_stage' => $to,
            'changed_by_id' => $userId,
            'note' => $note,
            'changed_at' => now(),
        ]);
    }

    private function logActivity(Lead $lead, string $type, string $title, ?string $body, ?int $userId): void
    {
        if (! Schema::hasTable('lead_activities')) {
            return;
        }

        LeadActivity::query()->create([
            'lead_id' => $lead->id,
            'activity_type' => $type,
            'title' => $title,
            'body' => $body,
            'created_by_id' => $userId,
            'occurred_at' => now(),
        ]);
    }

    private function recordConversion(Lead $lead, string $type): void
    {
        if (! Schema::hasTable('marketing_conversion_events')) {
            return;
        }

        MarketingConversionEvent::query()->create([
            'lead_id' => $lead->id,
            'conversion_type' => $type,
            'pipeline_stage' => $lead->pipeline_stage instanceof LeadPipelineStage ? $lead->pipeline_stage->value : null,
            'source' => $lead->lead_source ?? ($lead->source instanceof \BackedEnum ? $lead->source->value : (string) $lead->source),
            'campaign' => $lead->lead_campaign ?? $lead->campaign,
            'converted_at' => now(),
        ]);
    }
}

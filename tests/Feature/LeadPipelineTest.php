<?php

use App\Enums\LeadPipelineStage;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadPipelineStageHistory;
use App\Models\User;
use App\Services\Marketing\Pipeline\LeadPipelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('initializes pipeline on lead creation', function () {
    $lead = Lead::query()->create([
        'name' => 'Pipeline Test',
        'phone' => '9000000001',
        'service' => 'Physio',
        'source' => 'organic',
        'status' => LeadStatus::New,
    ]);

    expect($lead->fresh()->pipeline_stage)->toBe(LeadPipelineStage::NewLead)
        ->and(LeadPipelineStageHistory::query()->where('lead_id', $lead->id)->count())->toBe(1);
});

it('records stage movement history', function () {
    $lead = Lead::query()->create([
        'name' => 'Stage Move',
        'phone' => '9000000002',
        'service' => 'Nursing',
        'source' => 'organic',
        'status' => LeadStatus::New,
    ]);

    $user = User::factory()->create(['role' => 'admin']);
    app(LeadPipelineService::class)->moveStage($lead->fresh(), LeadPipelineStage::ProposalSent, $user, 'Sent quote');

    $lead->refresh();
    expect($lead->pipeline_stage)->toBe(LeadPipelineStage::ProposalSent)
        ->and($lead->status)->toBe(LeadStatus::Interested)
        ->and(LeadPipelineStageHistory::query()->where('lead_id', $lead->id)->count())->toBeGreaterThan(1);
});

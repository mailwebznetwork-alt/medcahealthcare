<?php

namespace App\Enums;

enum LeadPipelineStage: string
{
    case NewLead = 'new_lead';
    case Contacted = 'contacted';
    case AssessmentScheduled = 'assessment_scheduled';
    case ProposalSent = 'proposal_sent';
    case Converted = 'converted';
    case ActiveClient = 'active_client';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::NewLead => __('New Lead'),
            self::Contacted => __('Contacted'),
            self::AssessmentScheduled => __('Assessment Scheduled'),
            self::ProposalSent => __('Proposal Sent'),
            self::Converted => __('Converted'),
            self::ActiveClient => __('Active Client'),
            self::Lost => __('Lost'),
        };
    }

    public static function fromLegacyStatus(LeadStatus $status): self
    {
        return match ($status) {
            LeadStatus::New => self::NewLead,
            LeadStatus::Contacted => self::Contacted,
            LeadStatus::Interested => self::AssessmentScheduled,
            LeadStatus::Converted => self::Converted,
            LeadStatus::Closed => self::Lost,
        };
    }

    public function toLegacyStatus(): LeadStatus
    {
        return match ($this) {
            self::NewLead => LeadStatus::New,
            self::Contacted => LeadStatus::Contacted,
            self::AssessmentScheduled, self::ProposalSent => LeadStatus::Interested,
            self::Converted, self::ActiveClient => LeadStatus::Converted,
            self::Lost => LeadStatus::Closed,
        };
    }

    /**
     * @return list<string>
     */
    public static function conversionTypes(): array
    {
        return [
            'lead_created',
            'assessment_scheduled',
            'proposal_sent',
            'client_converted',
        ];
    }
}

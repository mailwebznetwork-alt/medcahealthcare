<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingClickEvent extends Model
{
    protected $fillable = [
        'event_type',
        'page_path',
        'page_title',
        'campaign',
        'source',
        'medium',
        'element_label',
        'destination_url',
        'device_type',
        'browser',
        'operating_system',
        'session_fingerprint',
        'lead_id',
        'meta',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}

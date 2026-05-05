<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleBusinessReview extends Model
{
    protected $fillable = [
        'integration_id',
        'review_id',
        'reviewer_name',
        'star_rating',
        'comment',
        'review_time',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'review_time' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }
}

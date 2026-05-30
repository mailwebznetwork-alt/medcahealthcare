<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteBacklink extends Model
{
    protected $fillable = [
        'referring_domain',
        'target_url',
        'anchor_text',
        'source',
        'status',
        'last_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'last_checked_at' => 'datetime',
        ];
    }
}

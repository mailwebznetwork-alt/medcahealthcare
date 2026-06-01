<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingAnalyticsDailyStat extends Model
{
    protected $fillable = [
        'stat_date',
        'metric_group',
        'metric_key',
        'metric_value',
        'dimensions',
    ];

    protected function casts(): array
    {
        return [
            'stat_date' => 'date',
            'dimensions' => 'array',
        ];
    }
}

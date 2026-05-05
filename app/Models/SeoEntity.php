<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoEntity extends Model
{
    protected $fillable = [
        'organization_name',
        'website',
        'default_language',
        'knowledge_graph_id',
        'social_profiles',
    ];

    protected function casts(): array
    {
        return [
            'social_profiles' => 'array',
        ];
    }
}

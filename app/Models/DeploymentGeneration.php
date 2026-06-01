<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentGeneration extends Model
{
    protected $fillable = [
        'blueprint_slug',
        'style_pack_slug',
        'theme_preset_slug',
        'layout_preset',
        'generated_page_slugs',
        'status',
        'generated_by_id',
    ];

    protected function casts(): array
    {
        return [
            'generated_page_slugs' => 'array',
        ];
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_id');
    }
}

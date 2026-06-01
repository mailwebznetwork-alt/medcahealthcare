<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockPreset extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'block_type',
        'target_block_slug',
        'settings_json',
        'is_builtin',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'settings_json' => 'array',
            'is_builtin' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}

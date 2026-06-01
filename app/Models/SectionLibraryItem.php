<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionLibraryItem extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'blocks_json',
        'style_pack_slug',
        'is_builtin',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'blocks_json' => 'array',
            'is_builtin' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}

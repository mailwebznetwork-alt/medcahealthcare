<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemePreset extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'shell',
        'is_builtin',
        'tokens',
        'branding',
        'header_preset',
        'layout_preset',
        'typography',
    ];

    protected function casts(): array
    {
        return [
            'is_builtin' => 'boolean',
            'tokens' => 'array',
            'branding' => 'array',
            'typography' => 'array',
        ];
    }
}

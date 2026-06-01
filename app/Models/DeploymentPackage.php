<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentPackage extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'version',
        'package_version',
        'manifest_json',
        'checksum',
        'cloned_from_id',
        'exported_by_id',
        'imported_by_id',
        'exported_at',
        'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'manifest_json' => 'array',
            'exported_at' => 'datetime',
            'imported_at' => 'datetime',
        ];
    }

    public function clonedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'cloned_from_id');
    }

    public function exportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by_id');
    }
}

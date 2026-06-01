<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlobalContentVariableSnapshot extends Model
{
    protected $fillable = [
        'version',
        'payload_json',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
            'version' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_id',
    'schema_type',
    'schema_json',
])]
class ServiceSchema extends Model
{
    protected $table = 'service_schema';

    protected function casts(): array
    {
        return [
            'schema_json' => 'array',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}

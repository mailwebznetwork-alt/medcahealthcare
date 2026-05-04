<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_id',
    'question',
    'answer',
])]
class ServiceFaq extends Model
{
    protected $table = 'service_faqs';

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}

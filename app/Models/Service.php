<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Enums\ServiceVisibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'title',
    'service_code',
    'short_summary',
    'description',
    'price_range',
    'featured_image',
    'icon',
    'gallery',
    'image_alt',
    'target_keywords',
    'ai_keywords',
    'quality_score',
    'is_active',
    'is_featured',
    'publish_status',
    'visibility',
    'sort_order',
])]
class Service extends Model
{
    /**
     * Block Factory / dynamic blocks resolve by stable code, never by slug.
     */
    public static function findByCode(string $code): ?self
    {
        return static::query()->where('service_code', $code)->first();
    }

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'target_keywords' => 'array',
            'ai_keywords' => 'array',
            'quality_score' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'publish_status' => PublishStatus::class,
            'visibility' => ServiceVisibility::class,
            'sort_order' => 'integer',
        ];
    }

    public function seo(): HasOne
    {
        return $this->hasOne(ServiceSeo::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(ServiceFaq::class);
    }

    public function schema(): HasOne
    {
        return $this->hasOne(ServiceSchema::class);
    }

    /**
     * GEO coverage — existing pin_codes rows only (no free-text pincodes).
     *
     * @return BelongsToMany<PinCode, $this>
     */
    public function pincodes(): BelongsToMany
    {
        return $this->belongsToMany(PinCode::class, 'service_pincodes')
            ->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoEntity extends Model
{
    protected $fillable = [
        'business_profile_id',
        'organization_name',
        'logo',
        'same_as',
        'meta_title',
        'meta_description',
        'og_image_url',
        'custom_json_ld',
        'google_place_id',
        'google_business_profile_url',
        'has_map_url',
        'entity_faqs',
        'hijack_strategy',
    ];

    /**
     * @return array<string, mixed>
     */
    public function hijackStrategies(): array
    {
        if (! is_string($this->hijack_strategy) || trim($this->hijack_strategy) === '') {
            return [];
        }

        try {
            $decoded = json_decode($this->hijack_strategy, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    protected function casts(): array
    {
        return [
            'same_as' => 'array',
            'custom_json_ld' => 'array',
            'entity_faqs' => 'array',
        ];
    }
}

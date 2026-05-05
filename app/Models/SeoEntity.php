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
    ];

    protected function casts(): array
    {
        return [
            'same_as' => 'array',
            'custom_json_ld' => 'array',
            'entity_faqs' => 'array',
        ];
    }
}

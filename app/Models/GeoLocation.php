<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoLocation extends Model
{
    protected $fillable = [
        'label',
        'latitude',
        'longitude',
        'radius_km',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_km' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function pincodes(): HasMany
    {
        return $this->hasMany(Pincode::class);
    }
}

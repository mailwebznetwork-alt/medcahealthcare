<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoLocation extends Model
{
    protected $fillable = [
        'business_profile_id',
        'latitude',
        'longitude',
        'radius_km',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_km' => 'integer',
        ];
    }

    /**
     * @return HasMany<PinCode, $this>
     */
    public function pinCodes(): HasMany
    {
        return $this->hasMany(PinCode::class);
    }
}

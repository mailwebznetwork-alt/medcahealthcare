<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessProfile extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'phone_e164',
        'country_code',
        'street_address',
        'city',
        'region',
        'postal_code',
        'website',
        'address',
    ];

    /**
     * @return HasMany<PinCode, $this>
     */
    public function pinCodes(): HasMany
    {
        return $this->hasMany(PinCode::class);
    }
}

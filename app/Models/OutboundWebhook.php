<?php

namespace App\Models;

use Database\Factories\OutboundWebhookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutboundWebhook extends Model
{
    /** @use HasFactory<OutboundWebhookFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'target_url',
        'http_method',
        'secret',
        'is_enabled',
        'payload_template',
        'custom_headers',
        'auth_bearer_token',
        'enforce_https',
        'max_retries',
        'timeout_seconds',
        'sort_order',
        'events',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'enforce_https' => 'boolean',
            'custom_headers' => 'array',
            'events' => 'array',
            'secret' => 'encrypted',
            'auth_bearer_token' => 'encrypted',
        ];
    }

    /**
     * @return HasMany<WebhookDelivery, $this>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }
}

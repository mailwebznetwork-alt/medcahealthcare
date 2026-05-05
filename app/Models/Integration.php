<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class Integration extends Model
{
    protected $fillable = [
        'name',
        'type',
        'credentials',
        'is_enabled',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_enabled', true);
    }

    public function getCredential(string $key): mixed
    {
        $credentials = $this->credentials;

        return is_array($credentials) ? ($credentials[$key] ?? null) : null;
    }

    public function getCredentialsAttribute(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = is_array($value) ? $value : json_decode((string) $value, true);
        if (! is_array($decoded)) {
            return [];
        }

        return $this->decryptArray($decoded);
    }

    public function setCredentialsAttribute(mixed $value): void
    {
        $credentials = is_array($value) ? $value : [];
        $this->attributes['credentials'] = json_encode(
            $this->encryptArray($credentials),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    private function encryptArray(array $credentials): array
    {
        $encrypted = [];

        foreach ($credentials as $key => $value) {
            if (is_array($value)) {
                $encrypted[$key] = $this->encryptArray($value);

                continue;
            }

            if ($value === null || $value === '') {
                $encrypted[$key] = null;

                continue;
            }

            $encrypted[$key] = Crypt::encryptString((string) $value);
        }

        return $encrypted;
    }

    private function decryptArray(array $credentials): array
    {
        $decrypted = [];

        foreach ($credentials as $key => $value) {
            if (is_array($value)) {
                $decrypted[$key] = $this->decryptArray($value);

                continue;
            }

            if ($value === null || $value === '') {
                $decrypted[$key] = null;

                continue;
            }

            try {
                $decrypted[$key] = Crypt::decryptString((string) $value);
            } catch (Throwable) {
                $decrypted[$key] = null;
            }
        }

        return $decrypted;
    }
}

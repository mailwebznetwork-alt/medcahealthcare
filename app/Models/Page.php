<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'keywords',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'aeo_question',
        'aeo_answer',
        'schema_json',
        'gtm_code',
        'pixel_code',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'schema_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Page $page): void {
            if (empty($page->uuid)) {
                $page->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Ordered tokens from page content: {{block:x}} / {{module:y}} lines.
     *
     * @return list<array{type: string, slug: string}>
     */
    public static function parseContentTokens(?string $content): array
    {
        if ($content === null || trim($content) === '') {
            return [];
        }

        preg_match_all('/\{\{\s*(block|module)\s*:\s*([^}]+?)\s*\}\}/', $content, $matches, PREG_SET_ORDER);
        $parts = [];
        foreach ($matches as $row) {
            $parts[] = [
                'type' => strtolower(trim($row[1])),
                'slug' => trim($row[2]),
            ];
        }

        return $parts;
    }

    /**
     * @param  list<array{type: string, slug: string}>  $parts
     */
    public static function buildContentFromParts(array $parts): string
    {
        $lines = [];
        foreach ($parts as $part) {
            $type = $part['type'];
            $slug = $part['slug'];
            $lines[] = '{{'.$type.':'.$slug.'}}';
        }

        return implode("\n", $lines);
    }

    /**
     * @return BelongsToMany<PinCode, $this>
     */
    public function pinCodes(): BelongsToMany
    {
        return $this->belongsToMany(PinCode::class, 'page_pin_codes')
            ->withPivot(['serviceability', 'delivery_charge', 'location_keywords'])
            ->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

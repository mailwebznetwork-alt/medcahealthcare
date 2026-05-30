<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteKeywordRanking extends Model
{
    protected $fillable = [
        'keyword',
        'position',
        'recorded_date',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'recorded_date' => 'date',
        ];
    }

    public static function normalizeKeyword(string $keyword): string
    {
        return mb_strtolower(trim($keyword));
    }

    public static function latestPositionForKeyword(string $keyword): ?int
    {
        $position = self::query()
            ->where('keyword', self::normalizeKeyword($keyword))
            ->orderByDesc('recorded_date')
            ->orderByDesc('id')
            ->value('position');

        return $position !== null ? (int) $position : null;
    }
}

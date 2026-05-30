<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'module_id',
    'field_name',
    'label',
    'field_type',
    'is_required',
    'sort_order',
    'settings',
])]
class FieldDefinition extends Model
{
    public const TYPE_TEXT = 'text';

    public const TYPE_TEXTAREA = 'textarea';

    public const TYPE_NUMBER = 'number';

    public const TYPE_BOOLEAN = 'boolean';

    public const TYPE_EMAIL = 'email';

    public const TYPE_URL = 'url';

    public const TYPE_DATE = 'date';

    public const TYPE_SELECT = 'select';

    /**
     * @return list<string>
     */
    public static function types(): array
    {
        return [
            self::TYPE_TEXT,
            self::TYPE_TEXTAREA,
            self::TYPE_NUMBER,
            self::TYPE_BOOLEAN,
            self::TYPE_EMAIL,
            self::TYPE_URL,
            self::TYPE_DATE,
            self::TYPE_SELECT,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function typeLabels(): array
    {
        return [
            self::TYPE_TEXT => 'Text',
            self::TYPE_TEXTAREA => 'Textarea',
            self::TYPE_NUMBER => 'Number',
            self::TYPE_BOOLEAN => 'Boolean',
            self::TYPE_EMAIL => 'Email',
            self::TYPE_URL => 'URL',
            self::TYPE_DATE => 'Date',
            self::TYPE_SELECT => 'Select',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (FieldDefinition $field): void {
            $field->field_name = Str::snake(Str::slug((string) $field->field_name, '_'));
        });
    }

    /**
     * @return BelongsTo<Module, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * @return list<string>
     */
    public function selectOptions(): array
    {
        $options = $this->settings['options'] ?? [];

        if (! is_array($options)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $option): string => trim((string) $option),
            $options
        ), static fn (string $option): bool => $option !== ''));
    }
}

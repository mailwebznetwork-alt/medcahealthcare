<?php

namespace App\Models;

use Database\Factories\ModuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'table_name',
    'settings',
    'is_active',
])]
class Module extends Model
{
    /** @use HasFactory<ModuleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Module $module): void {
            if (filled($module->slug)) {
                $module->slug = Str::slug($module->slug);
            }

            if ($module->isLegacy()) {
                return;
            }

            if (blank($module->table_name) && filled($module->slug)) {
                $module->table_name = self::tableNameForSlug($module->slug);
            }
        });

        static::updating(function (Module $module): void {
            if ($module->isLegacy()) {
                return;
            }

            if ($module->isDirty('slug') && ! $module->isDirty('table_name')) {
                $module->table_name = self::tableNameForSlug((string) $module->slug);
            }
        });
    }

    public function isLegacy(): bool
    {
        return (bool) data_get($this->settings, 'legacy', false);
    }

    public function usesJsonStorage(): bool
    {
        return data_get($this->settings, 'storage', 'column') === 'json';
    }

    public function usesColumnStorage(): bool
    {
        return ! $this->usesJsonStorage();
    }

    public function customFieldsColumn(): string
    {
        return (string) data_get($this->settings, 'custom_fields_column', 'custom_fields');
    }

    public static function tableNameForSlug(string $slug): string
    {
        return 'mod_'.Str::slug($slug, '_');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return HasMany<FieldDefinition, $this>
     */
    public function fieldDefinitions(): HasMany
    {
        return $this->hasMany(FieldDefinition::class)->orderBy('sort_order')->orderBy('id');
    }
}

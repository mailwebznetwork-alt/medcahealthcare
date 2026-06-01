<?php

namespace App\Services\Deployment;

use App\Models\Block;
use App\Models\BlockPreset;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BlockPresetRepository
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public function save(string $name, string $blockType, ?string $targetBlockSlug, array $settings, User $user): BlockPreset
    {
        $slug = str($name)->slug()->toString().'-'.now()->format('His');

        return BlockPreset::query()->create([
            'slug' => $slug,
            'name' => $name,
            'block_type' => $blockType,
            'target_block_slug' => $targetBlockSlug,
            'settings_json' => $settings,
            'is_builtin' => false,
            'created_by_id' => $user->id,
        ]);
    }

    public function applyToBlock(BlockPreset $preset, Block $block): void
    {
        $block->settings_json = array_replace_recursive(
            is_array($block->settings_json) ? $block->settings_json : [],
            is_array($preset->settings_json) ? $preset->settings_json : []
        );
        $block->save();
    }

    /**
     * @return array<string, mixed>
     */
    public function export(BlockPreset $preset): array
    {
        return [
            'slug' => $preset->slug,
            'name' => $preset->name,
            'block_type' => $preset->block_type,
            'target_block_slug' => $preset->target_block_slug,
            'settings_json' => $preset->settings_json,
            'exported_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function import(array $payload, User $user): BlockPreset
    {
        if (! is_array($payload['settings_json'] ?? null)) {
            throw ValidationException::withMessages(['import' => __('Invalid block preset payload.')]);
        }

        $slug = str((string) ($payload['slug'] ?? 'imported-block-preset'))->slug()->toString().'-'.now()->format('YmdHis');

        return BlockPreset::query()->create([
            'slug' => $slug,
            'name' => (string) ($payload['name'] ?? 'Imported Block Preset'),
            'block_type' => $payload['block_type'] ?? null,
            'target_block_slug' => $payload['target_block_slug'] ?? null,
            'settings_json' => $payload['settings_json'],
            'is_builtin' => false,
            'created_by_id' => $user->id,
        ]);
    }

    public function storeExportFile(BlockPreset $preset): string
    {
        $path = config('deployment_engine.storage.block_preset_exports', 'deployment/block-presets')
            .'/'.Str::slug($preset->slug).'-'.now()->format('YmdHis').'.json';

        Storage::disk('local')->put($path, json_encode($this->export($preset), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $path;
    }

    public function find(string $slug): ?BlockPreset
    {
        return BlockPreset::query()->where('slug', $slug)->first();
    }

    public function delete(BlockPreset $preset): void
    {
        if ($preset->is_builtin) {
            throw ValidationException::withMessages(['preset' => __('Built-in presets cannot be deleted.')]);
        }

        $preset->delete();
    }

    public function clone(BlockPreset $preset, string $newName, User $user): BlockPreset
    {
        return $this->save(
            $newName,
            (string) ($preset->block_type ?? ''),
            $preset->target_block_slug,
            is_array($preset->settings_json) ? $preset->settings_json : [],
            $user,
        );
    }
}

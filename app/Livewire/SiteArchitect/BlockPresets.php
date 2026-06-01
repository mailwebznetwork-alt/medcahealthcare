<?php

namespace App\Livewire\SiteArchitect;

use App\Models\Block;
use App\Models\BlockPreset;
use App\Policies\DeploymentEnginePolicy;
use App\Services\Deployment\BlockPresetRepository;
use App\Services\Deployment\BlockSettingsEditor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class BlockPresets extends Component
{
    public string $preset_name = '';

    public string $block_type = 'Hero';

    public string $target_block_slug = '';

    public string $style_variant = 'style_1';

    public string $import_json = '';

    public ?int $selected_preset_id = null;

    public ?string $preview_html = null;

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        abort_unless(app(DeploymentEnginePolicy::class)->manageBlockPresets(auth()->user()), 403);
    }

    public function createPreset(BlockPresetRepository $repository): void
    {
        if ($this->preset_name === '') {
            $this->errorMessage = __('Enter a preset name.');

            return;
        }

        $preset = $repository->save(
            $this->preset_name,
            $this->block_type,
            $this->target_block_slug !== '' ? $this->target_block_slug : null,
            [
                'style_variant' => $this->style_variant,
                'media' => [],
                'section' => [],
            ],
            auth()->user(),
        );

        $this->selected_preset_id = $preset->id;
        $this->statusMessage = __('Block preset saved.');
        $this->errorMessage = null;
    }

    public function applyPreset(int $presetId, BlockPresetRepository $repository): void
    {
        $preset = BlockPreset::query()->find($presetId);
        $block = Block::query()->where('block_slug', $preset?->target_block_slug)->first();

        if ($preset === null || $block === null) {
            $this->errorMessage = __('Preset or target block not found.');

            return;
        }

        $repository->applyToBlock($preset, $block);
        $this->statusMessage = __('Preset applied to block :slug.', ['slug' => $block->block_slug]);
    }

    public function clonePreset(int $presetId, BlockPresetRepository $repository): void
    {
        $preset = BlockPreset::query()->find($presetId);
        if ($preset === null) {
            return;
        }

        $clone = $repository->clone($preset, $preset->name.' Copy', auth()->user());
        $this->selected_preset_id = $clone->id;
        $this->statusMessage = __('Preset cloned.');
    }

    public function deletePreset(int $presetId, BlockPresetRepository $repository): void
    {
        $preset = BlockPreset::query()->find($presetId);
        if ($preset === null) {
            return;
        }

        try {
            $repository->delete($preset);
            if ($this->selected_preset_id === $presetId) {
                $this->selected_preset_id = null;
                $this->preview_html = null;
            }
            $this->statusMessage = __('Preset deleted.');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function exportPreset(int $presetId, BlockPresetRepository $repository): void
    {
        $preset = BlockPreset::query()->find($presetId);
        if ($preset === null) {
            return;
        }

        $this->import_json = json_encode($repository->export($preset), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->statusMessage = __('Preset exported to JSON field.');
    }

    public function importPreset(BlockPresetRepository $repository): void
    {
        $payload = json_decode($this->import_json, true);
        if (! is_array($payload)) {
            $this->errorMessage = __('Invalid JSON.');

            return;
        }

        try {
            $preset = $repository->import($payload, auth()->user());
            $this->selected_preset_id = $preset->id;
            $this->statusMessage = __('Preset imported.');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function previewPreset(int $presetId, BlockSettingsEditor $editor): void
    {
        $preset = BlockPreset::query()->find($presetId);
        $block = Block::query()->where('block_slug', $preset?->target_block_slug)->first();

        if ($preset === null || $block === null) {
            $this->errorMessage = __('Link a target block slug to preview this preset.');

            return;
        }

        $block->settings_json = array_replace_recursive(
            is_array($block->settings_json) ? $block->settings_json : [],
            is_array($preset->settings_json) ? $preset->settings_json : [],
        );

        $this->preview_html = $editor->previewHtml($block);
        $this->selected_preset_id = $presetId;
    }

    public function render(): View
    {
        return view('livewire.site-architect.block-presets', [
            'presets' => Schema::hasTable('block_presets')
                ? BlockPreset::query()->orderBy('name')->get()
                : collect(),
            'blocks' => Block::query()->where('is_active', true)->orderBy('block_slug')->get(['block_slug', 'block_name', 'block_type']),
            'styleVariants' => app(BlockSettingsEditor::class)->styleVariants(),
            'ready' => Schema::hasTable('block_presets'),
        ]);
    }
}

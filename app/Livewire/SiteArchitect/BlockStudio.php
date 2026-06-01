<?php

namespace App\Livewire\SiteArchitect;

use App\Models\Block;
use App\Policies\DeploymentEnginePolicy;
use App\Services\Deployment\BlockSettingsEditor;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class BlockStudio extends Component
{
    use WithFileUploads;

    public string $activePanel = 'media';

    public string $block_slug = '';

    public string $style_variant = 'style_1';

    /** @var array<string, string> */
    public array $media = [];

    /** @var array<string, mixed> */
    public array $section = [];

    /** @var array<string, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null> */
    public array $uploads = [];

    public ?string $preview_html = null;

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        abort_unless(app(DeploymentEnginePolicy::class)->manageBlockPresets(auth()->user()), 403);

        $first = Block::query()->where('is_active', true)->orderBy('block_slug')->value('block_slug');
        if (is_string($first)) {
            $this->block_slug = $first;
            $this->loadBlock();
        }
    }

    public function updatedBlockSlug(): void
    {
        $this->loadBlock();
    }

    public function loadBlock(): void
    {
        $block = $this->selectedBlock();
        if ($block === null) {
            return;
        }

        $settings = app(BlockSettingsEditor::class)->settings($block);
        $this->style_variant = (string) ($settings['style_variant'] ?? 'style_1');
        $this->media = is_array($settings['media'] ?? null) ? array_map('strval', $settings['media']) : [];
        $this->section = is_array($settings['section'] ?? null) ? $settings['section'] : [];

        foreach (app(BlockSettingsEditor::class)->mediaSlotsForBlock($block) as $slot) {
            if (! array_key_exists($slot, $this->media)) {
                $this->media[$slot] = '';
            }
        }

        foreach (app(BlockSettingsEditor::class)->sectionControlKeys() as $key) {
            if (! array_key_exists($key, $this->section)) {
                $this->section[$key] = str_starts_with($key, 'visibility_') ? true : '';
            }
        }
    }

    public function saveDraft(BlockSettingsEditor $editor): void
    {
        $block = $this->selectedBlock();
        if ($block === null) {
            $this->errorMessage = __('Select a block.');

            return;
        }

        foreach ($this->uploads as $slot => $file) {
            if ($file === null) {
                continue;
            }
            $path = $file->store('deployment/block-media', 'public');
            $this->media[$slot] = $path;
        }

        $editor->save($block, [
            'style_variant' => $this->style_variant,
            'media' => $this->media,
            'section' => $this->section,
        ]);

        $this->uploads = [];
        $this->statusMessage = __('Block settings saved to settings_json.');
        $this->errorMessage = null;
    }

    public function preview(BlockSettingsEditor $editor): void
    {
        $block = $this->selectedBlock();
        if ($block === null) {
            return;
        }

        $editor->save($block, [
            'style_variant' => $this->style_variant,
            'media' => $this->media,
            'section' => $this->section,
        ]);

        $this->preview_html = $editor->previewHtml($block->fresh());
    }

    public function removeMedia(string $slot): void
    {
        $this->media[$slot] = '';
        unset($this->uploads[$slot]);
    }

    public function render(BlockSettingsEditor $editor): View
    {
        $block = $this->selectedBlock();

        return view('livewire.site-architect.block-studio', [
            'blocks' => Block::query()->where('is_active', true)->orderBy('block_slug')->get(),
            'mediaSlots' => $block ? $editor->mediaSlotsForBlock($block) : [],
            'sectionKeys' => $editor->sectionControlKeys(),
            'styleVariants' => $editor->styleVariants(),
        ]);
    }

    private function selectedBlock(): ?Block
    {
        if ($this->block_slug === '') {
            return null;
        }

        return Block::query()->where('block_slug', $this->block_slug)->first();
    }
}

<?php

namespace App\Livewire\Settings;

use App\Models\ThemeConfiguration;
use App\Services\Theme\ThemeConfigRepository;
use App\Services\Theme\ThemeContrastValidator;
use App\Services\Theme\ThemeCssVariableBuilder;
use App\Services\Theme\ThemePresetRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class AppearanceSettings extends Component
{
    use WithFileUploads;

    public string $activeTab = 'branding';

    /** @var array<string, string> */
    public array $tokens = [];

    /** @var array<string, mixed> */
    public array $branding = [];

    /** @var array<string, mixed> */
    public array $typography = [];

    public string $header_preset = 'classic_healthcare';

    public string $layout_preset = 'contained';

    public string $preset_slug = '';

    public string $clone_name = '';

    public string $import_json = '';

    public $logo_upload;

    public $favicon_upload;

    public function mount(
        ThemeConfigRepository $repository,
        ThemePresetRegistry $presetRegistry,
    ): void {
        $user = auth()->user();
        if ($user === null || ! in_array(strtolower((string) $user->role), ['admin', 'super_admin'], true)) {
            abort(403);
        }

        if (! Schema::hasTable('theme_configurations')) {
            return;
        }

        $this->tokens = $repository->draftPublicTokens();
        $this->branding = $repository->draftBranding();
        $this->typography = $repository->draftTypography();
        $this->header_preset = $repository->draftHeaderPreset();
        $this->layout_preset = $repository->draftLayoutPreset();
        $this->preset_slug = $presetRegistry->builtinSlugs()[0] ?? '';
    }

    public function setTab(string $tab): void
    {
        $allowed = ['branding', 'colors', 'typography', 'buttons', 'cards', 'header', 'layout', 'presets', 'preview'];
        if (in_array($tab, $allowed, true)) {
            $this->activeTab = $tab;
        }
    }

    public function saveBranding(ThemeConfigRepository $repository): void
    {
        $this->validate([
            'branding.brand_name' => ['required', 'string', 'max:120'],
            'branding.tagline' => ['nullable', 'string', 'max:160'],
            'branding.contact_email' => ['nullable', 'email', 'max:190'],
            'branding.brand_url' => ['nullable', 'url', 'max:500'],
            'branding.whatsapp_url' => ['nullable', 'url', 'max:500'],
            'branding.primary_cta_text' => ['nullable', 'string', 'max:80'],
            'logo_upload' => ['nullable', 'image', 'max:2048'],
            'favicon_upload' => ['nullable', 'image', 'max:512'],
        ]);

        $payload = collect($this->branding)->only(config('theme_management.branding_fields', []))->all();

        if ($this->logo_upload) {
            $payload['logo_path'] = $repository->storeUploadedAsset('logo_path', $this->logo_upload);
        }

        if ($this->favicon_upload) {
            $payload['favicon_path'] = $repository->storeUploadedAsset('favicon_path', $this->favicon_upload);
        }

        $repository->saveDraftBranding($payload, auth()->user());
        $this->branding = $repository->draftBranding();
        $this->logo_upload = null;
        $this->favicon_upload = null;
        session()->flash('status', __('Branding draft saved.'));
    }

    public function saveColors(ThemeConfigRepository $repository): void
    {
        $repository->saveDraftPublicTokens($this->tokens, auth()->user());
        session()->flash('status', __('Color draft saved.'));
    }

    public function saveTypography(ThemeConfigRepository $repository): void
    {
        $repository->saveDraftMeta($this->header_preset, $this->layout_preset, $this->typography, auth()->user());
        session()->flash('status', __('Typography draft saved.'));
    }

    public function saveHeader(ThemeConfigRepository $repository): void
    {
        $repository->saveDraftMeta($this->header_preset, $this->layout_preset, $this->typography, auth()->user());
        session()->flash('status', __('Header preset draft saved.'));
    }

    public function saveLayout(ThemeConfigRepository $repository): void
    {
        $repository->saveDraftMeta($this->header_preset, $this->layout_preset, $this->typography, auth()->user());
        session()->flash('status', __('Layout preset draft saved.'));
    }

    public function applyPreset(ThemeConfigRepository $repository): void
    {
        if ($this->preset_slug === '') {
            throw ValidationException::withMessages(['preset_slug' => __('Select a preset.')]);
        }

        $repository->applyPresetToDraft($this->preset_slug, auth()->user());
        $this->tokens = $repository->draftPublicTokens();
        $this->typography = $repository->draftTypography();
        $this->header_preset = $repository->draftHeaderPreset();
        $this->layout_preset = $repository->draftLayoutPreset();
        session()->flash('status', __('Preset applied to draft.'));
    }

    public function resetDraft(ThemeConfigRepository $repository): void
    {
        $repository->resetDraft();
        $this->tokens = $repository->draftPublicTokens();
        $this->branding = $repository->draftBranding();
        $this->typography = $repository->draftTypography();
        $this->header_preset = $repository->draftHeaderPreset();
        $this->layout_preset = $repository->draftLayoutPreset();
        session()->flash('status', __('Draft changes discarded.'));
    }

    public function publish(ThemeConfigRepository $repository): void
    {
        $user = auth()->user();
        if ($user === null || strtolower((string) $user->role) !== 'super_admin') {
            abort(403);
        }

        $repository->publishDraft($user);
        Session::forget('theme_preview_public');
        $this->tokens = $repository->draftPublicTokens();
        $this->branding = $repository->draftBranding();
        session()->flash('status', __('Theme published to the public site.'));
    }

    public function enablePreview(): void
    {
        Session::put('theme_preview_public', true);
        session()->flash('status', __('Preview mode enabled. Open the public site in a new tab.'));
    }

    public function disablePreview(): void
    {
        Session::forget('theme_preview_public');
        session()->flash('status', __('Preview mode disabled.'));
    }

    public function clonePreset(ThemeConfigRepository $repository): void
    {
        $this->validate(['clone_name' => ['required', 'string', 'max:120']]);
        $repository->clonePreset($this->preset_slug, $this->clone_name, auth()->user());
        session()->flash('status', __('Preset cloned.'));
    }

    public function exportPreset(ThemeConfigRepository $repository): void
    {
        if ($this->preset_slug === '') {
            throw ValidationException::withMessages(['preset_slug' => __('Select a preset.')]);
        }

        $this->import_json = json_encode($repository->exportPreset($this->preset_slug), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        session()->flash('status', __('Preset exported to JSON field below.'));
    }

    public function importPreset(ThemeConfigRepository $repository): void
    {
        $payload = json_decode($this->import_json, true);
        if (! is_array($payload)) {
            throw ValidationException::withMessages(['import_json' => __('Invalid JSON payload.')]);
        }

        $repository->importPreset($payload, auth()->user());
        session()->flash('status', __('Preset imported.'));
    }

    /**
     * @return array<string, mixed>
     */
    public function previewData(
        ThemeCssVariableBuilder $cssBuilder,
        ThemeContrastValidator $contrastValidator,
    ): array {
        return [
            'css' => $cssBuilder->inlineStyleBlock($cssBuilder->publicVariables($this->tokens)),
            'contrast_errors' => $contrastValidator->validatePublicTokens($this->tokens),
        ];
    }

    public function render(
        ThemeConfigRepository $repository,
        ThemePresetRegistry $presetRegistry,
        ThemeCssVariableBuilder $cssBuilder,
        ThemeContrastValidator $contrastValidator,
    ): View {
        $config = Schema::hasTable('theme_configurations') ? ThemeConfiguration::current() : null;

        return view('livewire.settings.appearance-settings', [
            'headerPresets' => config('theme_management.header_presets', []),
            'layoutPresets' => config('theme_management.layout_presets', []),
            'fontWhitelist' => config('theme_management.font_whitelist', []),
            'presets' => Schema::hasTable('theme_presets') ? $presetRegistry->publicPresets() : collect(),
            'tokenKeys' => array_keys($repository->defaultPublicTokens()),
            'preview' => $this->previewData($cssBuilder, $contrastValidator),
            'configuration' => $config,
            'previewActive' => Session::get('theme_preview_public') === true,
            'canPublish' => auth()->check() && strtolower((string) auth()->user()?->role) === 'super_admin',
            'logoUrl' => $repository->assetUrl($this->branding['logo_path'] ?? null),
            'faviconUrl' => $repository->assetUrl($this->branding['favicon_path'] ?? null),
        ]);
    }
}

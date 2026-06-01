<?php

namespace App\Services\Deployment;

use App\Models\Block;
use App\Models\DeploymentPackage;
use App\Models\ThemeConfiguration;
use App\Models\User;
use App\Services\Theme\ThemeConfigRepository;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeploymentPackageImporter
{
    public function __construct(
        private readonly GlobalContentVariableRepository $globalVariables,
        private readonly ThemeConfigRepository $themeRepository,
        private readonly BlockPresetRepository $blockPresets,
        private readonly SectionLibraryRepository $sections,
    ) {}

    /**
     * @param  array<string, mixed>  $manifest
     */
    public function import(array $manifest, string $name, User $user, bool $applyThemeToDraft = true): DeploymentPackage
    {
        if (($manifest['format'] ?? '') !== 'markonminds.deployment-package') {
            throw ValidationException::withMessages(['import' => __('Invalid deployment package format.')]);
        }

        if (is_array($manifest['global_content_variables'] ?? null)) {
            $this->globalVariables->importPayload($manifest['global_content_variables'], $user);
        }

        if ($applyThemeToDraft && is_array($manifest['theme'] ?? null)) {
            $this->importThemeDraft($manifest['theme'], $manifest['style_pack']['slug'] ?? null, $user);
        }

        foreach (is_array($manifest['block_presets'] ?? null) ? $manifest['block_presets'] : [] as $presetPayload) {
            if (is_array($presetPayload)) {
                $this->blockPresets->import($presetPayload, $user);
            }
        }

        foreach (is_array($manifest['section_library'] ?? null) ? $manifest['section_library'] : [] as $sectionPayload) {
            if (is_array($sectionPayload)) {
                $this->sections->import($sectionPayload, $user);
            }
        }

        if (is_array($manifest['media_mapping'] ?? null)) {
            $this->importMediaMapping($manifest['media_mapping']);
        }

        $slug = str($name)->slug()->toString().'-'.now()->format('YmdHis');

        return DeploymentPackage::query()->create([
            'slug' => $slug,
            'name' => $name,
            'version' => (string) ($manifest['format_version'] ?? '1.0.0'),
            'package_version' => 1,
            'manifest_json' => $manifest,
            'checksum' => hash('sha256', json_encode($manifest)),
            'imported_by_id' => $user->id,
            'imported_at' => now(),
        ]);
    }

    public function importFromRecord(DeploymentPackage $package, User $user): DeploymentPackage
    {
        $manifest = is_array($package->manifest_json) ? $package->manifest_json : [];

        return $this->import($manifest, $package->name.' (reimport)', $user);
    }

    /**
     * @param  array<string, mixed>  $themePayload
     */
    private function importThemeDraft(array $themePayload, ?string $stylePackSlug, User $user): void
    {
        $config = ThemeConfiguration::current();
        $fill = [];

        if (is_array($themePayload['published_public'] ?? null)) {
            $fill['draft_public'] = $themePayload['published_public'];
        }
        if (is_array($themePayload['published_shape'] ?? null)) {
            $fill['draft_shape'] = $themePayload['published_shape'];
        }
        if (is_array($themePayload['branding'] ?? null)) {
            $fill['draft_branding'] = $themePayload['branding'];
        }
        if (is_array($themePayload['typography'] ?? null)) {
            $fill['draft_typography'] = $themePayload['typography'];
        }
        if (isset($themePayload['header_preset'])) {
            $fill['draft_header_preset'] = $themePayload['header_preset'];
        }
        if (isset($themePayload['layout_preset'])) {
            $fill['draft_layout_preset'] = $themePayload['layout_preset'];
        }
        if (is_string($stylePackSlug) && $stylePackSlug !== '') {
            $fill['draft_style_pack'] = $stylePackSlug;
        }

        $fill['draft_updated_at'] = now();
        $fill['updated_by_id'] = $user->id;

        $config->fill($fill)->save();
        ThemeConfiguration::forgetCache();
        GlobalContentVariableRepository::forgetCache();
    }

    /**
     * @param  array<string, array<string, mixed>>  $mapping
     */
    private function importMediaMapping(array $mapping): void
    {
        foreach ($mapping as $blockSlug => $media) {
            if (! is_array($media) || $media === []) {
                continue;
            }
            $block = Block::query()->where('block_slug', $blockSlug)->first();
            if ($block === null) {
                continue;
            }
            $settings = is_array($block->settings_json) ? $block->settings_json : [];
            $settings['media'] = array_replace_recursive($settings['media'] ?? [], $media);
            $block->settings_json = $settings;
            $block->save();
        }
    }
}

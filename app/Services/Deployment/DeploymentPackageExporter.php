<?php

namespace App\Services\Deployment;

use App\Models\Block;
use App\Models\BlockPreset;
use App\Models\DeploymentPackage;
use App\Models\SectionLibraryItem;
use App\Models\ThemeConfiguration;
use App\Models\User;
use App\Services\Theme\ThemeConfigRepository;
use App\Services\Theme\ThemePresetRegistry;
use Illuminate\Support\Str;

class DeploymentPackageExporter
{
    public function __construct(
        private readonly GlobalContentVariableRepository $globalVariables,
        private readonly ThemeConfigRepository $themeRepository,
        private readonly ThemePresetRegistry $themePresets,
        private readonly BlockPresetRepository $blockPresets,
        private readonly SectionLibraryRepository $sections,
    ) {}

    /**
     * @param  list<string>  $blueprintSlugs  Config blueprint slugs to include
     * @param  list<string>  $sectionSlugs
     * @param  list<string>  $blockPresetSlugs
     */
    public function export(
        string $name,
        ?string $stylePackSlug,
        array $blueprintSlugs,
        array $sectionSlugs,
        array $blockPresetSlugs,
        User $user,
        ?DeploymentPackage $cloneFrom = null,
    ): DeploymentPackage {
        $manifest = $this->buildManifest($stylePackSlug, $blueprintSlugs, $sectionSlugs, $blockPresetSlugs);

        $slug = str($name)->slug()->toString();
        $checksum = hash('sha256', json_encode($manifest));

        $existing = DeploymentPackage::query()->where('slug', $slug)->first();
        $packageVersion = ($existing?->package_version ?? 0) + 1;

        return DeploymentPackage::query()->create([
            'slug' => $slug.'-'.$packageVersion,
            'name' => $name,
            'version' => '1.0.0',
            'package_version' => $packageVersion,
            'manifest_json' => $manifest,
            'checksum' => $checksum,
            'cloned_from_id' => $cloneFrom?->id,
            'exported_by_id' => $user->id,
            'exported_at' => now(),
        ]);
    }

    public function clonePackage(DeploymentPackage $source, string $newName, User $user): DeploymentPackage
    {
        return $this->export(
            $newName,
            $source->manifest_json['style_pack']['slug'] ?? null,
            array_column($source->manifest_json['blueprints'] ?? [], 'slug'),
            array_column($source->manifest_json['section_library'] ?? [], 'slug'),
            array_column($source->manifest_json['block_presets'] ?? [], 'slug'),
            $user,
            $source,
        );
    }

    /**
     * @param  list<string>  $blueprintSlugs
     * @param  list<string>  $sectionSlugs
     * @param  list<string>  $blockPresetSlugs
     * @return array<string, mixed>
     */
    public function buildManifest(
        ?string $stylePackSlug,
        array $blueprintSlugs,
        array $sectionSlugs,
        array $blockPresetSlugs,
    ): array {
        $config = ThemeConfiguration::current();
        $stylePackSlug ??= $config->active_style_pack ?? $config->draft_style_pack
            ?? config('deployment_engine.default_style_pack');

        $blueprints = [];
        foreach ($blueprintSlugs as $slug) {
            $def = config("blueprints.{$slug}");
            if (is_array($def)) {
                $blueprints[] = array_merge(['slug' => $slug], $def);
            }
        }

        $sections = SectionLibraryItem::query()
            ->when($sectionSlugs !== [], fn ($q) => $q->whereIn('slug', $sectionSlugs))
            ->get()
            ->map(fn (SectionLibraryItem $item) => $this->sections->export($item))
            ->values()
            ->all();

        $presets = BlockPreset::query()
            ->when($blockPresetSlugs !== [], fn ($q) => $q->whereIn('slug', $blockPresetSlugs))
            ->get()
            ->map(fn (BlockPreset $p) => $this->blockPresets->export($p))
            ->values()
            ->all();

        $mediaMapping = Block::query()
            ->whereNotNull('settings_json')
            ->get(['block_slug', 'settings_json'])
            ->mapWithKeys(fn (Block $b) => [
                $b->block_slug => is_array($b->settings_json['media'] ?? null) ? $b->settings_json['media'] : [],
            ])
            ->filter(fn (array $m) => $m !== [])
            ->all();

        return [
            'format' => 'markonminds.deployment-package',
            'format_version' => '1.0.0',
            'exported_at' => now()->toIso8601String(),
            'global_content_variables' => $this->globalVariables->exportPayload(),
            'theme' => [
                'published_public' => $config->published_public,
                'published_shape' => $config->published_shape,
                'branding' => $config->branding,
                'typography' => $config->typography,
                'header_preset' => $config->header_preset,
                'layout_preset' => $config->layout_preset,
                'active_preset_slug' => $config->active_preset_slug,
            ],
            'style_pack' => [
                'slug' => $stylePackSlug,
                'definition' => config("style_packs.{$stylePackSlug}"),
            ],
            'blueprints' => $blueprints,
            'block_presets' => $presets,
            'section_library' => $sections,
            'media_mapping' => $mediaMapping,
            'theme_presets' => collect($this->themePresets->builtinSlugs())
                ->mapWithKeys(fn (string $slug) => [$slug => config("theme_presets.{$slug}")])
                ->all(),
        ];
    }
}

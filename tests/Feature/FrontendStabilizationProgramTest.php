<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Final forensic checks for the Frontend Stabilization Program (Phases 1–5).
 */
class FrontendStabilizationProgramTest extends TestCase
{
    public function test_phase1_css_pipeline_is_split_with_deprecated_shim(): void
    {
        $vite = file_get_contents(base_path('vite.config.js'));

        $this->assertStringContainsString("'resources/css/public/public.css'", $vite);
        $this->assertStringContainsString("'resources/css/admin/admin.css'", $vite);
        $this->assertStringNotContainsString("'resources/css/app.css'", $vite);

        $appLayout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $adminLayout = file_get_contents(resource_path('views/components/layouts/markonminds.blade.php'));

        $this->assertStringContainsString("resources/css/public/public.css", $appLayout);
        $this->assertStringContainsString("resources/css/admin/admin.css", $adminLayout);

        $shim = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('@deprecated', $shim);
        $this->assertStringContainsString("@import './admin/admin.css'", $shim);
    }

    public function test_phase2_public_layout_primitives_exist(): void
    {
        foreach ([
            'components/public/hero.blade.php',
            'components/public/section.blade.php',
            'components/public/content-shell.blade.php',
        ] as $path) {
            $this->assertFileExists(resource_path('views/'.$path), $path);
        }
    }

    public function test_phase3_block_governance_is_wired(): void
    {
        $this->assertFileExists(base_path('config/block_templates.php'));
        $this->assertFileExists(app_path('Services/Blocks/BlockTemplateSyncService.php'));
        $this->assertFileExists(app_path('Console/Commands/SyncBlockTemplatesCommand.php'));

        $templates = config('block_templates.templates');
        $this->assertGreaterThanOrEqual(20, count($templates));

        $gitTemplates = glob(resource_path('views/blocks/*/*.blade.php'));
        $this->assertGreaterThanOrEqual(20, count($gitTemplates ?: []));
    }

    public function test_phase4_theme_namespaces_are_separated(): void
    {
        $publicTokens = file_get_contents(resource_path('css/public/tokens.css'));
        $adminTokens = file_get_contents(resource_path('css/markonminds.css'));

        $this->assertStringContainsString('--medca-primary:', $publicTokens);
        $this->assertStringNotContainsString('--accent-gold:', $publicTokens);
        $this->assertStringContainsString('--mom-gold:', $adminTokens);
        $this->assertStringContainsString('--accent-gold: var(--mom-gold)', $adminTokens);

        $this->assertIsArray(config('theme.admin'));
        $this->assertIsArray(config('theme.public'));
    }

    public function test_phase5_component_kit_is_registered(): void
    {
        $this->assertFileExists(resource_path('views/components/admin/workspace.blade.php'));
        $this->assertFileExists(resource_path('views/components/admin/card.blade.php'));
        $this->assertFileExists(resource_path('views/components/public/card.blade.php'));
        $this->assertSame('admin.workspace', config('components.admin.workspace'));
    }

    public function test_vite_manifest_points_at_split_css_entries(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists($manifestPath);

        $manifest = json_decode(file_get_contents($manifestPath), true);

        $this->assertArrayHasKey('resources/css/public/public.css', $manifest);
        $this->assertArrayHasKey('resources/css/admin/admin.css', $manifest);
        $this->assertStringContainsString('public', $manifest['resources/css/public/public.css']['file']);
        $this->assertStringContainsString('admin', $manifest['resources/css/admin/admin.css']['file']);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class ThemeFoundationTest extends TestCase
{
    public function test_theme_config_declares_dual_shell_namespaces(): void
    {
        $theme = config('theme');

        $this->assertSame('mom', $theme['admin']['namespace']);
        $this->assertSame('medca', $theme['public']['namespace']);
        $this->assertArrayHasKey('--accent-gold', $theme['admin']['legacy_aliases']);
        $this->assertSame('--medca-primary', $theme['public']['tokens']['primary']);
    }

    public function test_public_tokens_use_medca_namespace_without_admin_collisions(): void
    {
        $css = file_get_contents(base_path('resources/css/public/tokens.css'));

        $this->assertStringContainsString('--medca-primary:', $css);
        $this->assertStringContainsString('--medca-text-primary:', $css);
        $this->assertStringNotContainsString('--accent-gold:', $css);
        $this->assertStringNotContainsString('--text-primary:', $css);
        $this->assertStringNotContainsString('--bg-app:', $css);
    }

    public function test_admin_tokens_define_mom_gold_canonical_namespace(): void
    {
        $css = file_get_contents(base_path('resources/css/markonminds.css'));

        $this->assertStringContainsString('--mom-gold: #c5a059', $css);
        $this->assertStringContainsString('--accent-gold: var(--mom-gold)', $css);
        $this->assertStringContainsString('--mom-surface: var(--bg-surface)', $css);
    }

    public function test_tailwind_mom_gold_maps_to_mom_namespace(): void
    {
        $config = file_get_contents(base_path('tailwind.config.js'));

        $this->assertStringContainsString("'mom-gold': 'var(--mom-gold)'", $config);
        $this->assertStringContainsString("'medca-primary': 'var(--medca-primary)'", $config);
        $this->assertStringNotContainsString("'mom-gold': 'var(--accent-gold)'", $config);
    }
}

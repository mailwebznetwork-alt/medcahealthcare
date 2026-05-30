<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase7ModernizationTest extends TestCase
{
    public function test_public_views_have_no_hardcoded_brand_hex(): void
    {
        $files = glob(resource_path('views/**/*.blade.php')) ?: [];
        $offenders = [];

        foreach ($files as $file) {
            if (str_contains($file, '/welcome.blade.php')) {
                continue;
            }

            $contents = file_get_contents($file);
            if (preg_match('/#0046ad|#001f5c|#012a7d|#001e5c/i', $contents)) {
                $offenders[] = str_replace(base_path().'/', '', $file);
            }
        }

        $this->assertSame([], $offenders, 'Hardcoded brand hex found: '.implode(', ', $offenders));
    }

    public function test_inline_style_blocks_removed_from_public_partials(): void
    {
        foreach ([
            'careers/partials/open-roles-listing.blade.php',
            'careers/partials/apply-panel.blade.php',
            'public/services/partials/services-carousel.blade.php',
            'public/services/partials/services-grid.blade.php',
            'public/services/partials/service-detail-carousel.blade.php',
        ] as $path) {
            $contents = file_get_contents(resource_path('views/'.$path));
            $this->assertStringNotContainsString('<style>', $contents, $path);
        }
    }

    public function test_extracted_public_component_classes_exist(): void
    {
        $css = file_get_contents(resource_path('css/public/components.css'));

        foreach ([
            '.medca-hero-gradient',
            '.medca-eyebrow',
            '.medca-cta-solid',
            '.mc-jobs-search',
            '.medca-svc-carousel',
            '.medca-detail-carousel',
            '.mc-apply-panel',
        ] as $selector) {
            $this->assertStringContainsString($selector, $css);
        }
    }

    public function test_site_architect_modules_use_admin_card_component(): void
    {
        foreach ([
            'site-architect/modules/create.blade.php',
            'site-architect/modules/edit.blade.php',
            'site-architect/modules/index.blade.php',
            'site-architect/modules/records/index.blade.php',
        ] as $path) {
            $contents = file_get_contents(resource_path('views/'.$path));
            $this->assertStringContainsString('<x-admin.card', $contents, $path);
        }
    }

    public function test_medca_navy_tokens_are_defined(): void
    {
        $tokens = file_get_contents(resource_path('css/public/tokens.css'));

        $this->assertStringContainsString('--medca-navy:', $tokens);
        $this->assertStringContainsString('--medca-primary-hover:', $tokens);
        $this->assertSame('#001f5c', config('medca.theme_color'));
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ComponentStandardizationTest extends TestCase
{
    public function test_components_config_declares_admin_and_public_kits(): void
    {
        $components = config('components');

        $this->assertSame('admin.workspace', $components['admin']['workspace']);
        $this->assertSame('public.card', $components['public']['card']);
        $this->assertSame('mom-cta-compact', $components['admin']['css']['cta_compact']);
    }

    public function test_admin_workspace_component_defines_tabstrip_toolbar_and_content_slots(): void
    {
        $source = file_get_contents(resource_path('views/components/admin/workspace.blade.php'));

        $this->assertStringContainsString('operations-workspace', $source);
        $this->assertStringContainsString('mom-backend-tabstrip', $source);
        $this->assertStringContainsString('@isset($tabs)', $source);
        $this->assertStringContainsString('@isset($toolbar)', $source);
    }

    public function test_admin_card_component_applies_mom_card_shell(): void
    {
        $html = Blade::render('<x-admin.card data-test="card">Card body</x-admin.card>');

        $this->assertStringContainsString('mom-card', $html);
        $this->assertStringContainsString('p-6', $html);
        $this->assertStringContainsString('Card body', $html);
    }

    public function test_public_card_component_applies_service_card_shell(): void
    {
        $html = Blade::render('<x-public.card data-test="card">Card body</x-public.card>');

        $this->assertStringContainsString('service-card', $html);
        $this->assertStringContainsString('Card body', $html);
    }

    public function test_admin_link_button_supports_compact_primary_variant(): void
    {
        $html = Blade::render('<x-admin.link-button size="compact" href="#">Save</x-admin.link-button>');

        $this->assertStringContainsString('mom-cta-primary', $html);
        $this->assertStringContainsString('mom-cta-compact', $html);
    }

    public function test_primary_button_public_variant_uses_btn_premium_class(): void
    {
        $html = Blade::render('<x-primary-button variant="public">Apply</x-primary-button>');

        $this->assertStringContainsString('btn-premium', $html);
        $this->assertStringNotContainsString('#0046ad', $html);
    }

    public function test_module_workspace_wrappers_delegate_to_admin_workspace(): void
    {
        foreach ([
            'components/operations/workspace.blade.php',
            'components/site-architect/workspace.blade.php',
            'components/growth-center/workspace.blade.php',
            'components/settings/shell.blade.php',
        ] as $path) {
            $source = file_get_contents(resource_path('views/'.$path));
            $this->assertStringContainsString('<x-admin.workspace', $source, $path);
        }
    }
}

<?php

use App\Models\Integration;
use App\Models\User;
use App\ModuleAccess;
use Illuminate\Support\Facades\Schema;

function integrationsAllModulesOn(): array
{
    return collect(ModuleAccess::keys())
        ->mapWithKeys(fn (string $key) => [$key => true])
        ->all();
}

it('lists integrations for admin users', function () {
    if (! Schema::hasTable('integrations')) {
        $this->markTestSkipped('Integrations table is not migrated.');
    }

    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'module_access' => integrationsAllModulesOn(),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->getJson(route('admin.settings.integrations.index'))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Integrations fetched successfully.');
});

it('denies integrations access for non-admin users', function () {
    if (! Schema::hasTable('integrations')) {
        $this->markTestSkipped('Integrations table is not migrated.');
    }

    $viewer = User::factory()->create([
        'email_verified_at' => now(),
        'module_access' => integrationsAllModulesOn(),
        'role' => 'viewer',
    ]);

    $this->actingAs($viewer)
        ->getJson(route('admin.settings.integrations.index'))
        ->assertStatus(403)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden.');
});

it('updates and masks credentials for openai integration', function () {
    if (! Schema::hasTable('integrations')) {
        $this->markTestSkipped('Integrations table is not migrated.');
    }

    $admin = User::factory()->create([
        'email_verified_at' => now(),
        'module_access' => integrationsAllModulesOn(),
        'role' => 'super_admin',
    ]);

    Integration::query()->updateOrCreate(
        ['name' => 'openai'],
        ['type' => 'ai', 'credentials' => [], 'is_enabled' => false]
    );

    $this->actingAs($admin)
        ->postJson(route('admin.settings.integrations.update', ['name' => 'openai']), [
            'is_enabled' => true,
            'credentials' => [
                'api_key' => 'sk-test-1234567890',
                'model' => 'gpt-4o-mini',
                'temperature' => 0.3,
            ],
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.is_enabled', true);

    $this->actingAs($admin)
        ->getJson(route('admin.settings.integrations.show', ['name' => 'openai']))
        ->assertOk()
        ->assertJsonPath('success', true);
});

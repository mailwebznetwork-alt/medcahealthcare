<?php

use App\Models\Module;
use App\Models\User;
use App\ModuleAccess;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    if (! Schema::hasTable('modules')) {
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_05_30_170000_create_dynamic_module_registry_tables.php']);
    }
});

function dynamicModuleAdmin(): User
{
    return User::factory()->create([
        'email_verified_at' => now(),
        'role' => 'admin',
        'module_access' => collect(ModuleAccess::keys())
            ->mapWithKeys(fn (string $k) => [$k => $k === ModuleAccess::SITE_ARCHITECT])
            ->all(),
    ]);
}

it('forbids module manager when user lacks site architect access', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'module_access' => collect(ModuleAccess::keys())
            ->mapWithKeys(fn (string $k) => [$k => $k === ModuleAccess::DASHBOARD])
            ->all(),
    ]);

    $this->actingAs($user)
        ->get(route('site-architect.modules.index'))
        ->assertForbidden();
});

it('allows admin to create a products module with dynamic table and records', function () {
    $user = dynamicModuleAdmin();

    $this->actingAs($user)
        ->post(route('site-architect.modules.store'), [
            'name' => 'Products',
            'slug' => 'products',
            'fields' => [
                [
                    'label' => 'Product name',
                    'field_name' => 'name',
                    'field_type' => 'text',
                    'is_required' => '1',
                ],
                [
                    'label' => 'Price',
                    'field_name' => 'price',
                    'field_type' => 'number',
                    'is_required' => '1',
                ],
                [
                    'label' => 'Description',
                    'field_name' => 'description',
                    'field_type' => 'textarea',
                    'is_required' => '0',
                ],
            ],
        ])
        ->assertRedirect();

    $module = Module::query()->where('slug', 'products')->first();
    expect($module)->not->toBeNull();
    expect($module->table_name)->toBe('mod_products');
    expect(Schema::hasTable('mod_products'))->toBeTrue();
    expect($module->fieldDefinitions)->toHaveCount(3);

    $this->actingAs($user)
        ->post(route('site-architect.modules.records.store', $module), [
            'fields' => [
                'name' => 'Premium Health Check',
                'price' => '4999.00',
                'description' => 'Full body screening package.',
            ],
        ])
        ->assertRedirect(route('site-architect.modules.records.index', $module));

    $this->actingAs($user)
        ->get(route('site-architect.modules.records.index', $module))
        ->assertOk()
        ->assertSee('Premium Health Check', false);
});

it('forbids non-admin from creating modules', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'role' => 'editor',
        'module_access' => collect(ModuleAccess::keys())
            ->mapWithKeys(fn (string $k) => [$k => $k === ModuleAccess::SITE_ARCHITECT])
            ->all(),
    ]);

    $this->actingAs($user)
        ->get(route('site-architect.modules.create'))
        ->assertForbidden();
});

it('shows module builder tab on site architect workspace', function () {
    $user = dynamicModuleAdmin();

    $this->actingAs($user)
        ->get(route('site-architect.modules.index'))
        ->assertOk()
        ->assertSee(__('Module Builder'), false)
        ->assertSee(__('Create new module'), false);
});

it('rejects non-numeric price on dynamic record store', function () {
    $user = dynamicModuleAdmin();

    $this->actingAs($user)
        ->post(route('site-architect.modules.store'), [
            'name' => 'Pricing Test',
            'slug' => 'pricing-test',
            'fields' => [
                [
                    'label' => 'Price',
                    'field_name' => 'price',
                    'field_type' => 'number',
                    'is_required' => '1',
                ],
            ],
        ])
        ->assertRedirect();

    $module = Module::query()->where('slug', 'pricing-test')->firstOrFail();

    $this->actingAs($user)
        ->post(route('site-architect.modules.records.store', $module), [
            'fields' => [
                'price' => 'not-a-number',
            ],
        ])
        ->assertSessionHasErrors('fields.price');

    $this->actingAs($user)
        ->post(route('site-architect.modules.records.store', $module), [
            'fields' => [
                'price' => '42.50',
            ],
        ])
        ->assertRedirect(route('site-architect.modules.records.index', $module));
});

it('drops database column when a field is removed from module schema', function () {
    $user = dynamicModuleAdmin();

    $this->actingAs($user)
        ->post(route('site-architect.modules.store'), [
            'name' => 'Cleanup Test',
            'slug' => 'cleanup-test',
            'fields' => [
                [
                    'label' => 'SKU',
                    'field_name' => 'sku',
                    'field_type' => 'text',
                    'is_required' => '0',
                ],
                [
                    'label' => 'Price',
                    'field_name' => 'price',
                    'field_type' => 'number',
                    'is_required' => '0',
                ],
            ],
        ])
        ->assertRedirect();

    $module = Module::query()->where('slug', 'cleanup-test')->firstOrFail();
    expect(Schema::hasColumn($module->table_name, 'sku'))->toBeTrue();

    $skuId = $module->fieldDefinitions()->where('field_name', 'sku')->value('id');
    $priceId = $module->fieldDefinitions()->where('field_name', 'price')->value('id');

    $this->actingAs($user)
        ->put(route('site-architect.modules.update', $module), [
            'name' => 'Cleanup Test',
            'is_active' => '1',
            'fields' => [
                [
                    'id' => $priceId,
                    'label' => 'Price',
                    'field_name' => 'price',
                    'field_type' => 'number',
                    'is_required' => '0',
                ],
            ],
        ])
        ->assertRedirect(route('site-architect.modules.edit', $module));

    expect(Schema::hasColumn($module->table_name, 'sku'))->toBeFalse();
    expect(Schema::hasColumn($module->table_name, 'price'))->toBeTrue();
    expect($module->fresh()->fieldDefinitions)->toHaveCount(1);
});

it('maps record form inputs to dynamic column names', function () {
    $user = dynamicModuleAdmin();

    $this->actingAs($user)
        ->post(route('site-architect.modules.store'), [
            'name' => 'UI Map Test',
            'slug' => 'ui-map-test',
            'fields' => [
                [
                    'label' => 'SKU',
                    'field_name' => 'sku',
                    'field_type' => 'text',
                    'is_required' => '0',
                ],
            ],
        ])
        ->assertRedirect();

    $module = Module::query()->where('slug', 'ui-map-test')->firstOrFail();

    $this->actingAs($user)
        ->get(route('site-architect.modules.records.create', $module))
        ->assertOk()
        ->assertSee('name="fields[sku]"', false);
});

it('renders module create form without server error', function () {
    $user = dynamicModuleAdmin();

    $this->actingAs($user)
        ->get(route('site-architect.modules.create'))
        ->assertOk()
        ->assertSee(__('Create new module'), false)
        ->assertSee(__('Custom fields'), false);
});

it('lists custom modules in site architect insert catalog', function () {
    $user = dynamicModuleAdmin();

    $this->actingAs($user)
        ->post(route('site-architect.modules.store'), [
            'name' => 'Team Members',
            'slug' => 'team-members',
            'fields' => [
                [
                    'label' => 'Name',
                    'field_name' => 'name',
                    'field_type' => 'text',
                    'is_required' => '1',
                ],
            ],
        ])
        ->assertRedirect();

    $options = app(\App\Services\DynamicModules\DynamicModuleInsertCatalog::class)->forDropdown();
    expect(collect($options)->pluck('key'))->toContain('team-members');
});

it('runs the module builder verification artisan command successfully', function () {
    $this->artisan('app:verify-module-builder')
        ->assertSuccessful()
        ->expectsOutputToContain('ALL CHECKS PASSED');
});

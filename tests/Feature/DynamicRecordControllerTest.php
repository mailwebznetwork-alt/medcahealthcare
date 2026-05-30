<?php

use App\Models\Module;
use App\Models\User;
use App\ModuleAccess;
use App\Services\DynamicModules\DynamicRecordRepository;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    if (! Schema::hasTable('modules')) {
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_05_30_170000_create_dynamic_module_registry_tables.php']);
    }
});

function dynamicRecordAdmin(): User
{
    return User::factory()->create([
        'email_verified_at' => now(),
        'role' => 'admin',
        'module_access' => collect(ModuleAccess::keys())
            ->mapWithKeys(fn (string $k) => [$k => $k === ModuleAccess::SITE_ARCHITECT])
            ->all(),
    ]);
}

it('persists dynamic module records using fields payload and validates before save', function () {
    $user = dynamicRecordAdmin();

    $this->actingAs($user)
        ->post(route('site-architect.modules.store'), [
            'name' => 'Inventory',
            'slug' => 'inventory',
            'fields' => [
                [
                    'label' => 'SKU',
                    'field_name' => 'sku',
                    'field_type' => 'text',
                    'is_required' => '1',
                ],
                [
                    'label' => 'Quantity',
                    'field_name' => 'quantity',
                    'field_type' => 'number',
                    'is_required' => '1',
                ],
            ],
        ])
        ->assertRedirect();

    $module = Module::query()->where('slug', 'inventory')->firstOrFail();

    $this->actingAs($user)
        ->post(route('site-architect.modules.records.store', $module), [
            'fields' => [
                'sku' => 'MED-001',
                'quantity' => '10',
            ],
        ])
        ->assertRedirect(route('site-architect.modules.records.index', $module));

    $record = app(DynamicRecordRepository::class)->find($module, 1);
    expect($record)->not->toBeNull();
    expect($record->sku)->toBe('MED-001');
    expect((float) $record->quantity)->toBe(10.0);

    $this->actingAs($user)
        ->put(route('site-architect.modules.records.update', [$module, 1]), [
            'fields' => [
                'sku' => 'MED-002',
                'quantity' => '25',
            ],
        ])
        ->assertRedirect(route('site-architect.modules.records.index', $module));

    $updated = app(DynamicRecordRepository::class)->find($module, 1);
    expect($updated->sku)->toBe('MED-002');
});

it('rejects invalid dynamic record payload with fields namespace errors', function () {
    $user = dynamicRecordAdmin();

    $this->actingAs($user)
        ->post(route('site-architect.modules.store'), [
            'name' => 'Validators',
            'slug' => 'validators',
            'fields' => [
                [
                    'label' => 'Score',
                    'field_name' => 'score',
                    'field_type' => 'number',
                    'is_required' => '1',
                ],
            ],
        ]);

    $module = Module::query()->where('slug', 'validators')->firstOrFail();

    $this->actingAs($user)
        ->post(route('site-architect.modules.records.store', $module), [
            'fields' => [
                'score' => 'abc',
            ],
        ])
        ->assertSessionHasErrors('fields.score');
});

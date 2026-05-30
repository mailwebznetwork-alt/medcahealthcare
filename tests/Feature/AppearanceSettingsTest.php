<?php

use App\Models\User;
use App\ModuleAccess;
use App\Services\Theme\ThemeConfigRepository;
use Database\Seeders\ThemePresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemePresetSeeder::class);
});

it('allows admins to open appearance settings', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'module_access' => ModuleAccess::defaultGrants(),
    ]);

    $this->actingAs($user)
        ->get(route('settings.appearance'))
        ->assertSuccessful()
        ->assertSee(__('Appearance'));
});

it('saves branding draft without publishing', function () {
    $user = User::factory()->create(['role' => 'admin', 'module_access' => ModuleAccess::defaultGrants()]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Settings\AppearanceSettings::class)
        ->set('branding.brand_name', 'Draft Medca')
        ->call('saveBranding')
        ->assertHasNoErrors();

    expect(config('medca.brand_name'))->not->toBe('Draft Medca');
    expect(app(ThemeConfigRepository::class)->draftBranding()['brand_name'])->toBe('Draft Medca');
});

it('blocks non-admins from appearance settings', function () {
    $user = User::factory()->create(['role' => 'editor', 'module_access' => ModuleAccess::defaultGrants()]);

    $this->actingAs($user)
        ->get(route('settings.appearance'))
        ->assertForbidden();
});

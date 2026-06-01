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

it('saves color draft with normalized hex', function () {
    $user = User::factory()->create(['role' => 'admin', 'module_access' => ModuleAccess::defaultGrants()]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Settings\AppearanceSettings::class)
        ->set('tokens.primary', '#FF0000')
        ->call('saveColors')
        ->assertHasNoErrors()
        ->assertSet('statusMessage', fn ($msg) => is_string($msg) && str_contains($msg, 'Color draft saved'));

    expect(app(ThemeConfigRepository::class)->draftPublicTokens()['primary'])->toBe('#ff0000');
});

it('saves custom google font typography', function () {
    $user = User::factory()->create(['role' => 'admin', 'module_access' => ModuleAccess::defaultGrants()]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Settings\AppearanceSettings::class)
        ->set('heading_font_mode', 'custom')
        ->set('custom_heading_font', 'Playfair Display')
        ->set('body_font_mode', 'custom')
        ->set('custom_body_font', 'Work Sans')
        ->call('saveTypography')
        ->assertHasNoErrors();

    $typography = app(ThemeConfigRepository::class)->draftTypography();
    expect($typography['heading_font'])->toBe('Playfair Display')
        ->and($typography['body_font'])->toBe('Work Sans');
});

it('allows admin to publish theme', function () {
    $user = User::factory()->create(['role' => 'admin', 'module_access' => ModuleAccess::defaultGrants()]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Settings\AppearanceSettings::class)
        ->set('tokens.primary', '#112233')
        ->call('saveColors')
        ->call('publish')
        ->assertHasNoErrors();

    expect(app(ThemeConfigRepository::class)->publishedPublicTokens()['primary'])->toBe('#112233');
});

it('blocks non-admins from appearance settings', function () {
    $user = User::factory()->create(['role' => 'editor', 'module_access' => ModuleAccess::defaultGrants()]);

    $this->actingAs($user)
        ->get(route('settings.appearance'))
        ->assertForbidden();
});

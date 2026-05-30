<?php

use App\Models\User;
use App\ModuleAccess;
use App\Services\Theme\ThemeConfigRepository;
use Database\Seeders\ThemePresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemePresetSeeder::class);
});

it('enables session preview for authorized users', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'module_access' => ModuleAccess::defaultGrants(),
    ]);

    $repo = app(ThemeConfigRepository::class);
    $repo->saveDraftPublicTokens(['primary' => '#abcdef'], $user);

    $this->actingAs($user)
        ->post(route('settings.appearance.preview.enable'), ['redirect' => '/'])
        ->assertRedirect('/');

    expect(session('theme_preview_public'))->toBeTrue();

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('#abcdef', false);
});

it('disables preview session', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'module_access' => ModuleAccess::defaultGrants(),
    ]);

    session(['theme_preview_public' => true]);

    $this->actingAs($user)
        ->post(route('settings.appearance.preview.disable'))
        ->assertRedirect(route('settings.appearance'));

    expect(session('theme_preview_public'))->toBeNull();
});

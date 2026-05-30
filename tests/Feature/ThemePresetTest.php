<?php

use App\Models\ThemePreset;
use App\Models\User;
use App\Services\Theme\ThemeConfigRepository;
use App\Services\Theme\ThemePresetRegistry;
use Database\Seeders\ThemePresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemePresetSeeder::class);
});

it('seeds five built-in presets', function () {
    expect(ThemePreset::query()->where('is_builtin', true)->count())->toBe(5);
});

it('applies preset tokens to draft', function () {
    $user = User::factory()->create(['role' => 'admin']);
    app(ThemeConfigRepository::class)->applyPresetToDraft('forest_green', $user);

    $tokens = app(ThemeConfigRepository::class)->draftPublicTokens();

    expect($tokens['primary'])->toBe('#15803d');
});

it('clones and exports presets', function () {
    $user = User::factory()->create(['role' => 'super_admin']);
    $repo = app(ThemeConfigRepository::class);

    $clone = $repo->clonePreset('clinical_blue', 'My Clinical Copy', $user);
    expect($clone->slug)->toContain('my-clinical-copy');

    $export = $repo->exportPreset('clinical_blue');
    expect($export['tokens']['primary'])->toBe('#0055ff');

    $imported = $repo->importPreset($export, $user);
    expect(app(ThemePresetRegistry::class)->findBySlug($imported->slug))->not->toBeNull();
});

it('resets builtin preset via config definition', function () {
    $registry = app(ThemePresetRegistry::class);
    expect($registry->builtinDefinition('modern_purple')['name'])->toBe('Modern Purple');
});

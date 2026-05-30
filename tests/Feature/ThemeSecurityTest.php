<?php

use App\Models\ThemeConfiguration;
use App\Models\User;
use App\ModuleAccess;
use App\Services\Theme\ThemeConfigRepository;
use App\Services\Theme\ThemeContrastValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('rejects invalid color tokens', function () {
    $validator = app(ThemeContrastValidator::class);
    expect($validator->isValidColor('not-a-color'))->toBeFalse();
});

it('blocks publish with invalid draft colors', function () {
    $user = User::factory()->create(['role' => 'super_admin']);
    $repo = app(ThemeConfigRepository::class);

    ThemeConfiguration::current()->update(['draft_public' => ['primary' => 'bad']]);

    expect(fn () => $repo->publishDraft($user))->toThrow(ValidationException::class);
});

it('restricts publish to super admins via livewire', function () {
    $admin = User::factory()->create(['role' => 'admin', 'module_access' => ModuleAccess::defaultGrants()]);

    Livewire\Livewire::actingAs($admin)
        ->test(\App\Livewire\Settings\AppearanceSettings::class)
        ->call('publish')
        ->assertStatus(403);
});

it('rejects non-whitelisted fonts', function () {
    $user = User::factory()->create(['role' => 'admin']);

    expect(fn () => app(ThemeConfigRepository::class)->saveDraftMeta(
        'classic_healthcare',
        'contained',
        ['heading_font' => 'Comic Sans MS', 'body_font' => 'Comic Sans MS'],
        $user,
    ))->toThrow(ValidationException::class);
});

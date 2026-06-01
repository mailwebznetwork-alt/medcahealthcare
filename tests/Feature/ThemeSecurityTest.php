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

it('blocks editor from appearance settings', function () {
    $user = User::factory()->create(['role' => 'editor', 'module_access' => ModuleAccess::defaultGrants()]);

    $this->actingAs($user)
        ->get(route('settings.appearance'))
        ->assertForbidden();
});

it('rejects invalid font names', function () {
    $user = User::factory()->create(['role' => 'admin']);

    expect(fn () => app(ThemeConfigRepository::class)->saveDraftTypography(
        ['heading_font' => '<script>', 'body_font' => 'Valid Font'],
        $user,
    ))->toThrow(ValidationException::class);
});

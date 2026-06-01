<?php

use App\Models\ThemeConfiguration;
use App\Models\ThemePreset;
use App\Models\User;
use App\ModuleAccess;
use App\Services\Theme\ThemeConfigRepository;
use App\Services\Theme\ThemeCssVariableBuilder;
use App\Services\Theme\ThemeResolver;
use Database\Seeders\ThemePresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemePresetSeeder::class);
});

it('creates theme tables and singleton configuration', function () {
    expect(ThemePreset::query()->count())->toBe(5)
        ->and(ThemeConfiguration::current())->not->toBeNull();
});

it('maps published tokens to medca css variables', function () {
    $repo = app(ThemeConfigRepository::class);
    $repo->saveDraftPublicTokens(['primary' => '#112233'], User::factory()->create(['role' => 'super_admin']));
    $repo->publishDraft(User::factory()->create(['role' => 'super_admin']));

    $vars = app(ThemeCssVariableBuilder::class)->publicVariables($repo->publishedPublicTokens());

    expect($vars['--medca-primary'])->toBe('#112233');
});

it('injects theme vars component on public layout', function () {
    $this->get('/')->assertSuccessful()
        ->assertSee('medca-theme-vars', false);
});

it('keeps admin layout isolated from public theme injection', function () {
    $admin = User::factory()->create([
        'role' => 'super_admin',
        'module_access' => ModuleAccess::defaultGrants(),
    ]);

    $this->actingAs($admin)
        ->get(route('settings.appearance'))
        ->assertSuccessful()
        ->assertDontSee('id="medca-theme-vars"', false);
});

it('google fonts href ignores typography scale metadata', function () {
    $href = app(\App\Services\Theme\ThemeResolver::class)->googleFontsHref();

    expect($href)->toContain('Plus+Jakarta+Sans')
        ->and($href)->not->toContain('family=default')
        ->and($href)->not->toContain('family=1.5');
});

it('resolves layout classes from configuration', function () {
    $repo = app(ThemeConfigRepository::class);
    $user = User::factory()->create(['role' => 'admin']);
    $repo->saveDraftMeta('classic_healthcare', 'wide', [], $user);
    $config = ThemeConfiguration::current();
    $config->update([
        'layout_preset' => 'wide',
        'draft_layout_preset' => null,
    ]);

    expect(app(ThemeResolver::class)->layoutMainClasses())->toContain('max-w-7xl');
});

<?php

use App\Models\GlobalContentVariable;
use App\Models\Page;
use App\Models\SectionLibraryItem;
use App\Models\User;
use App\ModuleAccess;
use App\Services\ContentParser;
use App\Services\Deployment\DeploymentPackageExporter;
use App\Services\Deployment\DeploymentPackageImporter;
use App\Services\Deployment\GlobalContentInterpolator;
use App\Services\Deployment\GlobalContentVariableRepository;
use App\Services\Deployment\SectionLibraryRepository;
use Database\Seeders\ThemePresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemePresetSeeder::class);
    $this->artisan('migrate', ['--path' => 'database/migrations/2026_05_31_140000_phase85_supplemental_patch.php']);
});

it('interpolates global content variable tokens', function () {
    $user = User::factory()->create(['role' => 'admin']);
    app(GlobalContentVariableRepository::class)->sync(['company_name' => 'Medca Test Clinic'], $user);

    $html = app(GlobalContentInterpolator::class)->interpolate('Welcome to {{ company_name }}');

    expect($html)->toBe('Welcome to Medca Test Clinic');
});

it('renders section tokens as multiple blocks', function () {
    SectionLibraryItem::query()->create([
        'slug' => 'test-intro',
        'name' => 'Test Intro',
        'blocks_json' => [
            ['slug' => 'hero-home', 'style_variant' => 'style_1'],
        ],
        'is_builtin' => true,
    ]);

    \App\Models\Block::query()->create([
        'block_slug' => 'hero-home',
        'block_name' => 'Hero',
        'block_type' => 'Hero',
        'code' => '<p>Hero block</p>',
        'is_active' => true,
    ]);

    $html = ContentParser::parse("{{section:test-intro}}");

    expect($html)->toContain('Hero block');
});

it('inserts section into page as standard block tokens', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $section = app(SectionLibraryRepository::class)->save('Intro', [
        ['slug' => 'hero-home', 'style_variant' => 'style_2'],
    ], null, null, $user);

    $page = Page::query()->create([
        'title' => 'Test',
        'slug' => 'patch-test',
        'content' => '',
        'is_active' => true,
    ]);

    app(SectionLibraryRepository::class)->insertIntoPage($page, $section->slug);
    $page->refresh();

    expect($page->content)->toContain('{{block:hero-home}}')
        ->and($page->block_overrides_json['hero-home']['style_variant'])->toBe('style_2');
});

it('exports and imports deployment package manifest', function () {
    $user = User::factory()->create(['role' => 'admin']);
    GlobalContentVariable::query()->create([
        'key' => 'company_name',
        'label' => 'Company Name',
        'value' => 'Package Co',
    ]);

    $exported = app(DeploymentPackageExporter::class)->export(
        'Test Package',
        'healthcare_professional',
        ['home_healthcare'],
        [],
        [],
        $user,
    );

    expect($exported->manifest_json['format'])->toBe('markonminds.deployment-package')
        ->and($exported->manifest_json['global_content_variables']['company_name'])->toBe('Package Co');

    $imported = app(DeploymentPackageImporter::class)->import(
        $exported->manifest_json,
        'Imported Client',
        $user,
    );

    expect($imported->name)->toBe('Imported Client')
        ->and(app(GlobalContentVariableRepository::class)->resolved()['company_name'])->toBe('Package Co');
});

it('exposes global content settings to admins', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'module_access' => ModuleAccess::defaultGrants(),
    ]);

    $this->actingAs($user)
        ->get(route('settings.global-content'))
        ->assertSuccessful()
        ->assertSee(__('Global Content Variables'));
});

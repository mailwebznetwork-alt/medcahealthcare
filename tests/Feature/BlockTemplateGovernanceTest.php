<?php

use App\Models\Block;
use App\Models\Page;
use App\Models\User;
use App\ModuleAccess;
use App\Services\Blocks\BlockTemplateSyncService;
use Database\Seeders\MedcaCareersPageSeeder;
use Database\Seeders\MedcaPublicPagesSeeder;
use Livewire\Livewire;

it('registers twenty-two managed block templates in config', function () {
    expect(config('block_templates.templates'))->toHaveCount(22);
});

it('syncs Git templates into the database with include-based code', function () {
    $result = app(BlockTemplateSyncService::class)->sync(
        slugs: ['hero-home'],
        backup: false,
    );

    expect($result['synced'])->toContain('hero-home');

    $block = Block::query()->where('block_slug', 'hero-home')->first();

    expect($block)->not->toBeNull()
        ->and($block->is_managed)->toBeTrue()
        ->and($block->code)->toBe("@include('blocks.home.hero-home')");
});

it('renders synced managed blocks on the public home page', function () {
    app(BlockTemplateSyncService::class)->sync(categories: ['home'], backup: false);

    Page::query()->updateOrCreate(
        ['slug' => 'home'],
        [
            'title' => 'Home',
            'content' => '{{block:hero-home}}',
            'is_active' => true,
        ]
    );

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Premium home healthcare', false);
});

it('restores soft-deleted managed blocks when syncing', function () {
    app(BlockTemplateSyncService::class)->sync(slugs: ['hero-home'], backup: false);

    $block = Block::query()->where('block_slug', 'hero-home')->firstOrFail();
    $block->delete();

    expect(Block::query()->where('block_slug', 'hero-home')->exists())->toBeFalse();

    app(BlockTemplateSyncService::class)->sync(slugs: ['hero-home'], backup: false);

    expect(Block::query()->where('block_slug', 'hero-home')->exists())->toBeTrue();
});

it('prevents deleting managed blocks from block factory', function () {
    app(BlockTemplateSyncService::class)->sync(slugs: ['hero-home'], backup: false);

    $user = User::factory()->create([
        'role' => 'super_admin',
        'module_access' => collect(ModuleAccess::keys())
            ->mapWithKeys(static fn (string $key): array => [$key => true])
            ->all(),
    ]);
    $block = Block::query()->where('block_slug', 'hero-home')->firstOrFail();

    Livewire::actingAs($user)
        ->test(\App\Livewire\SiteArchitect\BlockFactory::class)
        ->call('deleteBlock', $block->id)
        ->assertHasNoErrors();

    expect(Block::query()->where('block_slug', 'hero-home')->exists())->toBeTrue();
});

it('writes a JSON backup before syncing when backup is enabled', function () {
    app(BlockTemplateSyncService::class)->sync(slugs: ['hero-home'], backup: true);

    $backups = app(BlockTemplateSyncService::class)->listBackups();

    expect($backups)->not->toBeEmpty();
    expect(file_exists($backups[0]))->toBeTrue();
});

it('seeds marketing pages through the template sync service', function () {
    $this->seed(MedcaPublicPagesSeeder::class);

    expect(Block::query()->where('block_slug', 'hero-home')->value('is_managed'))->toBeTrue();
    expect(Block::query()->where('block_slug', 'hero-home')->value('code'))->toBe("@include('blocks.home.hero-home')");
});

it('seeds careers blocks as managed templates', function () {
    $this->seed(MedcaCareersPageSeeder::class);

    expect(Block::query()->where('block_slug', 'careers-open-roles')->value('is_managed'))->toBeTrue();
    expect(Block::query()->where('block_slug', 'careers-open-roles')->value('code'))
        ->toBe("@include('blocks.careers.open-roles-listing', ['vacancies' => \$vacancies ?? collect()])");
});

it('can restore blocks from a JSON backup file', function () {
    app(BlockTemplateSyncService::class)->sync(slugs: ['hero-home'], backup: false);

    $block = Block::query()->where('block_slug', 'hero-home')->firstOrFail();
    $block->update(['code' => '<section>Custom backup marker</section>']);

    $path = app(BlockTemplateSyncService::class)->backupBlocks(['hero-home']);

    $block->update(['code' => '<section>Changed after backup</section>']);

    app(BlockTemplateSyncService::class)->restoreFromBackup($path);

    expect(Block::query()->where('block_slug', 'hero-home')->value('code'))
        ->toBe('<section>Custom backup marker</section>');
});

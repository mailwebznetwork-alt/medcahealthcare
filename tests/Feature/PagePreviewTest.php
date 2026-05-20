<?php

use App\Enums\PageLayoutMode;
use App\Models\Block;
use App\Models\Page;
use App\Models\Service;
use App\Models\User;
use App\ModuleAccess;

function siteArchitectPreviewUser(): User
{
    return User::factory()->create([
        'email_verified_at' => now(),
        'role' => 'manager',
        'module_access' => collect(ModuleAccess::keys())
            ->mapWithKeys(fn (string $k) => [$k => $k === ModuleAccess::SITE_ARCHITECT])
            ->all(),
    ]);
}

it('previews a service detail page without error when blocks use $service', function () {
    $user = siteArchitectPreviewUser();

    Block::query()->updateOrCreate(
        ['block_slug' => 'service-detail-hero'],
        [
            'block_name' => 'Service detail — hero',
            'code' => '<h1 data-preview-hero>{{ $service->title }}</h1>',
            'is_active' => true,
        ]
    );

    $service = Service::factory()->create([
        'service_code' => 'homenursing-services',
        'title' => 'Home Nursing Services',
    ]);

    $page = Page::factory()->create([
        'slug' => 'service-homenursing-services',
        'content' => '{{block:service-detail-hero}}',
        'layout_mode' => PageLayoutMode::Canvas,
        'is_active' => true,
    ]);

    $service->update(['detail_page_id' => $page->id]);

    $this->actingAs($user)
        ->get(route('site-architect.pages.preview', $page))
        ->assertSuccessful()
        ->assertSee('data-preview-hero', false)
        ->assertSee('Home Nursing Services', false);
});

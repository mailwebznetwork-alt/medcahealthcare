<?php

use App\Livewire\SiteArchitect\Pages;
use App\Models\Page;
use App\Models\SeoEntity;
use App\Models\User;
use App\ModuleAccess;
use Livewire\Livewire;

it('surfaces matching hijack strategies in the page editor and applies them', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'role' => 'manager',
        'module_access' => collect(ModuleAccess::keys())
            ->mapWithKeys(fn (string $k) => [$k => $k === ModuleAccess::SITE_ARCHITECT])
            ->all(),
    ]);

    SeoEntity::query()->create([
        'organization_name' => 'Medca Health Care',
        'hijack_strategy' => json_encode([
            '42' => [
                'keyword' => 'arekere blood test',
                'hijack_priority' => 8,
                'competitor_name' => 'Rival Labs',
                'position_gap' => 5,
                'meta_title' => 'Arekere Blood Test at Home | Medca',
                'meta_description' => 'Book trusted home blood tests in Arekere with Medca Health Care.',
                'h1_suggestion' => 'Home Blood Tests in Arekere',
                'content_changes' => ['Add local trust badge above fold'],
            ],
        ], JSON_THROW_ON_ERROR),
    ]);

    $page = Page::factory()->create([
        'focus_keywords' => ['arekere blood test'],
    ]);

    Livewire::actingAs($user)
        ->test(Pages::class)
        ->call('startEdit', $page->id)
        ->assertSee('Growth Center — Hijack strategies')
        ->assertSee('Arekere Blood Test at Home | Medca')
        ->call('applyHijackStrategy', '42')
        ->assertSet('meta_title', 'Arekere Blood Test at Home | Medca')
        ->assertSet('meta_description', 'Book trusted home blood tests in Arekere with Medca Health Care.')
        ->assertSet('h1', 'Home Blood Tests in Arekere');
});

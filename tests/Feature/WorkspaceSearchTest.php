<?php

use App\Models\Service;
use App\Models\User;
use App\ModuleAccess;

it('redirects guests from workspace search', function () {
    $this->get(route('workspace.search', ['q' => 'ab']))->assertRedirect();
});

it('shows matching services for users with operations access', function () {
    $user = User::factory()->create(['role' => 'manager']);

    Service::query()->create([
        'title' => 'Unique Physio Search Token',
        'service_code' => 'physio-unique-'.uniqid(),
        'publish_status' => 'published',
        'visibility' => 'public',
    ]);

    $this->actingAs($user)
        ->get(route('workspace.search', ['q' => 'Unique Physio']))
        ->assertSuccessful()
        ->assertSee('Unique Physio Search Token', false);
});

it('hides operations hits when the user lacks operations module access', function () {
    $access = array_fill_keys(ModuleAccess::keys(), false);
    $access[ModuleAccess::DASHBOARD] = true;
    $access[ModuleAccess::SITE_ARCHITECT] = true;

    $user = User::factory()->create([
        'role' => 'viewer',
        'module_access' => $access,
    ]);

    Service::query()->create([
        'title' => 'Hidden Service For Search',
        'service_code' => 'hidden-svc-'.uniqid(),
        'publish_status' => 'published',
        'visibility' => 'public',
    ]);

    $this->actingAs($user)
        ->get(route('workspace.search', ['q' => 'Hidden Service']))
        ->assertSuccessful()
        ->assertDontSee('Hidden Service For Search', false);
});

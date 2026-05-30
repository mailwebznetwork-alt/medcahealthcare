<?php

use App\Models\OutboundWebhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

it('sends managed outbound webhooks and logs delivery when a lead is created', function () {
    if (! Schema::hasTable('outbound_webhooks')) {
        $this->markTestSkipped('Outbound webhooks table is not migrated.');
    }

    Http::fake([
        'https://managed.example.test/hook' => Http::response(['ok' => true], 200),
    ]);

    OutboundWebhook::factory()->create([
        'name' => 'Primary hook',
        'target_url' => 'https://managed.example.test/hook',
        'http_method' => 'POST',
        'is_enabled' => true,
        'events' => ['lead.created'],
        'secret' => 'whsec_managed_test',
    ]);

    $this->postJson('/api/leads', [
        'name' => 'Test User',
        'phone' => '9'.fake()->unique()->numerify('#########'),
        'service' => 'Home care',
    ], ['X-API-KEY' => config('security.lead_api_key')])->assertCreated();

    expect(WebhookDelivery::query()->where('event_key', 'lead.created')->where('success', true)->count())->toBeGreaterThanOrEqual(1);

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://managed.example.test/hook'
            && str_contains((string) $request->body(), 'lead.created')
            && $request->hasHeader('X-Webhook-Event', 'lead.created');
    });
});

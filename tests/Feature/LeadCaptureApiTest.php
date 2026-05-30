<?php

use App\Models\Lead;

/**
 * @return array<string, string>
 */
function leadApiHeaders(): array
{
    $key = config('security.lead_api_key');

    return ['X-API-KEY' => is_string($key) && $key !== '' ? $key : 'test-lead-api-key'];
}

test('post api leads rejects missing api key', function () {
    config(['security.lead_api_key' => 'secret-lead-key']);

    $this->postJson('/api/leads', [
        'name' => 'No Key',
        'phone' => '9876500000',
        'service' => 'Consultation',
    ])->assertUnauthorized();
});

test('post api leads creates organic lead when source omitted', function () {
    $response = $this->postJson('/api/leads', [
        'name' => 'Test Patient',
        'phone' => '9876543210',
        'service' => 'Consultation',
        'message' => 'Hello',
    ], leadApiHeaders());

    $response->assertCreated()
        ->assertJsonPath('duplicate', false)
        ->assertJsonStructure(['data' => ['uuid', 'id']]);

    $lead = Lead::query()->first();
    expect($lead)->not->toBeNull()
        ->and($lead->source->value)->toBe('organic');
});

test('post api leads maps utm to source and campaign', function () {
    $response = $this->postJson('/api/leads', [
        'name' => 'Utm User',
        'phone' => '9123456780',
        'service' => 'Home visit',
        'utm_source' => 'facebook',
        'utm_campaign' => 'monsoon_2026',
    ], leadApiHeaders());

    $response->assertCreated();
    $lead = Lead::query()->where('phone', '9123456780')->first();
    expect($lead->source->value)->toBe('meta_ads')
        ->and($lead->campaign)->toBe('monsoon_2026');
});

test('post api leads returns duplicate for rapid same phone and service', function () {
    $this->postJson('/api/leads', [
        'name' => 'Dup',
        'phone' => '9000000001',
        'service' => 'Lab test',
    ], leadApiHeaders())->assertCreated();

    $response = $this->postJson('/api/leads', [
        'name' => 'Dup Again',
        'phone' => '9000000001',
        'service' => 'Lab test',
    ], leadApiHeaders());

    $response->assertOk()
        ->assertJsonPath('duplicate', true);
});

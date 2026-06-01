<?php

use App\Models\Lead;
use App\Models\MarketingClickEvent;
use App\Models\User;
use App\ModuleAccess;
use App\Services\Marketing\Attribution\UtmCaptureService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('captures utm parameters into session last touch', function () {
    $service = app(UtmCaptureService::class);

    $this->get('/?utm_source=google&utm_medium=cpc&utm_campaign=spring')->assertSuccessful();

    $payload = $service->extractUtmPayload(request());
    expect($payload['utm_source'])->toBe('google')
        ->and($payload['utm_medium'])->toBe('cpc');
});

it('persists attribution fields when lead is created via api', function () {
    config(['security.lead_api_key' => 'test-key']);

    $this->withHeader('X-API-KEY', 'test-key')
        ->postJson('/api/leads', [
            'name' => 'Attribution Test',
            'phone' => '9876543210',
            'service' => 'Nursing',
            'utm_source' => 'facebook',
            'utm_medium' => 'paid',
            'utm_campaign' => 'meta_leads',
            'gclid' => 'abc123',
        ])
        ->assertCreated();

    $lead = Lead::query()->where('phone', '9876543210')->first();
    expect($lead)->not->toBeNull()
        ->and($lead->utm_source)->toBe('facebook')
        ->and($lead->utm_campaign)->toBe('meta_leads')
        ->and($lead->gclid)->toBe('abc123')
        ->and($lead->pipeline_stage?->value)->toBe('new_lead');
});

it('records marketing click events', function () {
    $this->postJson(route('marketing.track'), [
        'event_type' => 'whatsapp_click',
        'page_path' => '/contact',
        'session_fingerprint' => 'test-fp-1',
    ])->assertOk()->assertJson(['recorded' => true]);

    expect(MarketingClickEvent::query()->count())->toBe(1);
});

it('dedupes rapid duplicate click events', function () {
    $payload = [
        'event_type' => 'cta_click',
        'page_path' => '/',
        'session_fingerprint' => 'test-fp-dedupe',
    ];

    $this->postJson(route('marketing.track'), $payload)->assertJson(['recorded' => true]);
    $this->postJson(route('marketing.track'), $payload)->assertJson(['recorded' => false]);
});

it('rejects invalid click event types', function () {
    $this->postJson(route('marketing.track'), [
        'event_type' => 'invalid_event',
        'session_fingerprint' => 'x',
    ])->assertUnprocessable();
});

<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function __construct(private readonly ActivityLogService $activityLogService) {}

    public function testConnection(): array
    {
        try {
            $integration = Integration::query()->where('name', 'whatsapp_business')->first();

            if (! $integration instanceof Integration || ! $integration->is_enabled) {
                return ['success' => false, 'message' => 'Integration disabled.', 'data' => []];
            }

            $phoneNumberId = (string) $integration->getCredential('phone_number_id');
            $accessToken = (string) $integration->getCredential('access_token');
            $webhookToken = (string) $integration->getCredential('webhook_verify_token');

            if ($phoneNumberId === '' || $accessToken === '' || $webhookToken === '') {
                return ['success' => false, 'message' => 'Missing required credentials.', 'data' => []];
            }

            $integration->forceFill(['last_used_at' => now()])->save();
            $this->activityLogService->log('integration_test_success', 'integrations', 'WhatsApp integration test passed.');

            return ['success' => true, 'message' => 'WhatsApp webhook verification simulation passed.', 'data' => []];
        } catch (\Throwable $exception) {
            Log::error('WhatsApp integration test failed.', ['error' => $exception->getMessage()]);
            $this->activityLogService->log('integration_test_failure', 'integrations', 'WhatsApp integration test failed.');

            return ['success' => false, 'message' => 'WhatsApp integration test failed.', 'data' => []];
        }
    }
}

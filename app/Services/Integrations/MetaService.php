<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaService
{
    public function __construct(private readonly ActivityLogService $activityLogService) {}

    public function testConnection(): array
    {
        try {
            $integration = Integration::query()->where('name', 'meta_ads')->first();

            if (! $integration instanceof Integration || ! $integration->is_enabled) {
                return ['success' => false, 'message' => 'Integration disabled.', 'data' => []];
            }

            $pixelId = (string) $integration->getCredential('pixel_id');
            $accessToken = (string) $integration->getCredential('access_token');

            if ($pixelId === '' || $accessToken === '') {
                return ['success' => false, 'message' => 'Missing required credentials.', 'data' => []];
            }

            $response = Http::timeout(8)
                ->connectTimeout(5)
                ->get("https://graph.facebook.com/v20.0/{$pixelId}", [
                    'access_token' => $accessToken,
                    'fields' => 'id,name',
                ]);

            if (! $response->successful()) {
                $this->activityLogService->log('integration_test_failure', 'integrations', 'Meta integration test failed.');

                return ['success' => false, 'message' => 'Meta connection failed.', 'data' => ['status' => $response->status()]];
            }

            $integration->forceFill(['last_used_at' => now()])->save();
            $this->activityLogService->log('integration_test_success', 'integrations', 'Meta integration test passed.');

            return ['success' => true, 'message' => 'Meta connection successful.', 'data' => ['status' => $response->status()]];
        } catch (\Throwable $exception) {
            Log::error('Meta integration test failed.', ['error' => $exception->getMessage()]);
            $this->activityLogService->log('integration_test_failure', 'integrations', 'Meta integration test failed.');

            return ['success' => false, 'message' => 'Meta integration test failed.', 'data' => []];
        }
    }
}

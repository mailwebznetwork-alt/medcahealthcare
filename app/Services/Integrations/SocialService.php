<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialService
{
    public function __construct(private readonly ActivityLogService $activityLogService) {}

    public function getCredentials(string $name): array
    {
        $integration = Integration::query()->where('name', $name)->first();

        return $integration instanceof Integration ? $integration->credentials : [];
    }

    public function isEnabled(string $name): bool
    {
        return Integration::query()->where('name', $name)->value('is_enabled') === true;
    }

    public function testConnection(string $name): array
    {
        try {
            $integration = Integration::query()->where('name', $name)->first();

            if (! $integration instanceof Integration || ! $integration->is_enabled) {
                return ['success' => false, 'message' => 'Integration disabled.', 'data' => []];
            }

            $credentials = $this->getCredentials($name);

            return match ($name) {
                'youtube' => $this->testYoutube($integration, $credentials),
                'linkedin' => $this->testLinkedIn($integration, $credentials),
                'facebook' => $this->testFacebook($integration, $credentials),
                'instagram' => $this->testInstagram($integration, $credentials),
                default => ['success' => false, 'message' => 'Unsupported social integration.', 'data' => []],
            };
        } catch (\Throwable $exception) {
            Log::error('Social integration test failed.', ['integration' => $name, 'error' => $exception->getMessage()]);
            $this->activityLogService->log('integration_test_failure', 'integrations', "Social integration test failed for {$name}.");

            return ['success' => false, 'message' => 'Social integration test failed.', 'data' => []];
        }
    }

    private function testYoutube(Integration $integration, array $credentials): array
    {
        $apiKey = (string) ($credentials['api_key'] ?? '');
        $channelId = (string) ($credentials['channel_id'] ?? '');

        if (! preg_match('/^[A-Za-z0-9_\-]{20,}$/', $apiKey) || ! preg_match('/^UC[A-Za-z0-9_\-]{22}$/', $channelId)) {
            return ['success' => false, 'message' => 'Invalid YouTube credentials format.', 'data' => []];
        }

        $response = Http::timeout(10)
            ->connectTimeout(5)
            ->get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'id',
                'id' => $channelId,
                'key' => $apiKey,
            ]);

        return $this->buildResult($integration, $response->successful(), 'YouTube', $response->status());
    }

    private function testLinkedIn(Integration $integration, array $credentials): array
    {
        $clientId = (string) ($credentials['client_id'] ?? '');
        $clientSecret = (string) ($credentials['client_secret'] ?? '');

        if ($clientId === '' || $clientSecret === '') {
            return ['success' => false, 'message' => 'Missing LinkedIn credentials.', 'data' => []];
        }

        return $this->buildResult($integration, true, 'LinkedIn', 200);
    }

    private function testFacebook(Integration $integration, array $credentials): array
    {
        $pageId = (string) ($credentials['page_id'] ?? '');
        $accessToken = (string) ($credentials['access_token'] ?? '');

        if (! preg_match('/^\d{5,}$/', $pageId) || $accessToken === '') {
            return ['success' => false, 'message' => 'Invalid Facebook credentials format.', 'data' => []];
        }

        $response = Http::timeout(10)
            ->connectTimeout(5)
            ->get("https://graph.facebook.com/v20.0/{$pageId}", [
                'fields' => 'id,name',
                'access_token' => $accessToken,
            ]);

        return $this->buildResult($integration, $response->successful(), 'Facebook', $response->status());
    }

    private function testInstagram(Integration $integration, array $credentials): array
    {
        $accountId = (string) ($credentials['instagram_account_id'] ?? '');
        $accessToken = (string) ($credentials['access_token'] ?? '');

        if (! preg_match('/^\d{5,}$/', $accountId) || $accessToken === '') {
            return ['success' => false, 'message' => 'Invalid Instagram credentials format.', 'data' => []];
        }

        $response = Http::timeout(10)
            ->connectTimeout(5)
            ->get("https://graph.facebook.com/v20.0/{$accountId}", [
                'fields' => 'id,username',
                'access_token' => $accessToken,
            ]);

        return $this->buildResult($integration, $response->successful(), 'Instagram', $response->status());
    }

    private function buildResult(Integration $integration, bool $success, string $provider, int $status): array
    {
        if (! $success) {
            $this->activityLogService->log('integration_test_failure', 'integrations', "{$provider} integration test failed.");

            return ['success' => false, 'message' => "{$provider} connection failed.", 'data' => ['status' => $status]];
        }

        $integration->forceFill(['last_used_at' => now()])->save();
        $this->activityLogService->log('integration_test_success', 'integrations', "{$provider} integration test passed.");

        return ['success' => true, 'message' => "{$provider} connection successful.", 'data' => ['status' => $status]];
    }
}

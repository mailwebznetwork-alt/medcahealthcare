<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StorageService
{
    public function __construct(private readonly ActivityLogService $activityLogService) {}

    public function testConnection(): array
    {
        try {
            $aws = Integration::query()->where('name', 'aws_s3')->first();
            $cloudflare = Integration::query()->where('name', 'cloudflare')->first();

            $awsOk = true;
            $cloudflareOk = true;

            if ($aws instanceof Integration && $aws->is_enabled) {
                $awsOk = $this->validateAwsCredentials($aws);
            }

            if ($cloudflare instanceof Integration && $cloudflare->is_enabled) {
                $cloudflareOk = $this->validateCloudflareCredentials($cloudflare);
            }

            if (! $awsOk || ! $cloudflareOk) {
                $this->activityLogService->log('integration_test_failure', 'integrations', 'Storage integration test failed.');

                return ['success' => false, 'message' => 'Storage integration test failed.', 'data' => []];
            }

            if ($aws instanceof Integration) {
                $aws->forceFill(['last_used_at' => now()])->save();
            }
            if ($cloudflare instanceof Integration) {
                $cloudflare->forceFill(['last_used_at' => now()])->save();
            }

            $this->activityLogService->log('integration_test_success', 'integrations', 'Storage integration test passed.');

            return ['success' => true, 'message' => 'Storage integration is valid.', 'data' => []];
        } catch (\Throwable $exception) {
            Log::error('Storage integration test failed.', ['error' => $exception->getMessage()]);
            $this->activityLogService->log('integration_test_failure', 'integrations', 'Storage integration test failed.');

            return ['success' => false, 'message' => 'Storage integration test failed.', 'data' => []];
        }
    }

    private function validateAwsCredentials(Integration $integration): bool
    {
        $key = (string) $integration->getCredential('key');
        $secret = (string) $integration->getCredential('secret');
        $region = (string) $integration->getCredential('region');
        $bucket = (string) $integration->getCredential('bucket');

        return $key !== '' && $secret !== '' && $region !== '' && $bucket !== '';
    }

    private function validateCloudflareCredentials(Integration $integration): bool
    {
        $apiToken = (string) $integration->getCredential('api_token');
        $zoneId = (string) $integration->getCredential('zone_id');

        if ($apiToken === '' || $zoneId === '') {
            return false;
        }

        $response = Http::timeout(8)
            ->connectTimeout(5)
            ->withToken($apiToken)
            ->get("https://api.cloudflare.com/client/v4/zones/{$zoneId}");

        return $response->successful();
    }
}

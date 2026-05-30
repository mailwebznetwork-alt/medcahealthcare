<?php

namespace App\Services\Marketing;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleAdsReportService
{
    /**
     * @return array{campaigns: list<array{name: string, clicks: int, cost: float, conversions: float}>, note: string|null, configured: bool}
     */
    public function fetchSummary(): array
    {
        if (! $this->isConfigured()) {
            return [
                'campaigns' => [],
                'note' => __('Connect Google Ads API credentials (developer token, OAuth client, refresh token, customer ID) in .env for live campaign metrics. Event-level conversions remain visible in GA4.'),
                'configured' => false,
            ];
        }

        $ttl = max(60, (int) config('marketing.google_ads_cache_ttl', 3600));

        return Cache::remember('marketing.google_ads.summary', $ttl, function (): array {
            try {
                $accessToken = $this->fetchAccessToken();
                if ($accessToken === null) {
                    return [
                        'campaigns' => [],
                        'note' => __('Google Ads OAuth token exchange failed. Verify client ID, client secret, and refresh token.'),
                        'configured' => true,
                    ];
                }

                $campaigns = $this->fetchCampaignMetrics($accessToken);

                return [
                    'campaigns' => $campaigns,
                    'note' => $campaigns === []
                        ? __('Google Ads API connected but no enabled campaigns returned for the last 30 days.')
                        : null,
                    'configured' => true,
                ];
            } catch (Throwable $e) {
                Log::warning('Google Ads summary failed', ['message' => $e->getMessage()]);

                return [
                    'campaigns' => [],
                    'note' => $e->getMessage(),
                    'configured' => true,
                ];
            }
        });
    }

    private function isConfigured(): bool
    {
        foreach ([
            'marketing.google_ads_developer_token',
            'marketing.google_ads_client_id',
            'marketing.google_ads_client_secret',
            'marketing.google_ads_refresh_token',
            'marketing.google_ads_customer_id',
        ] as $key) {
            $value = config($key);
            if (! is_string($value) || trim($value) === '') {
                return false;
            }
        }

        return true;
    }

    private function fetchAccessToken(): ?string
    {
        $response = Http::asForm()
            ->timeout(15)
            ->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'refresh_token',
                'client_id' => config('marketing.google_ads_client_id'),
                'client_secret' => config('marketing.google_ads_client_secret'),
                'refresh_token' => config('marketing.google_ads_refresh_token'),
            ]);

        if (! $response->successful()) {
            Log::warning('Google Ads OAuth token exchange failed', [
                'status' => $response->status(),
                'body_preview' => mb_substr($response->body(), 0, 500),
            ]);

            return null;
        }

        $token = $response->json('access_token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    /**
     * @return list<array{name: string, clicks: int, cost: float, conversions: float}>
     */
    private function fetchCampaignMetrics(string $accessToken): array
    {
        $customerId = $this->normalizeCustomerId((string) config('marketing.google_ads_customer_id'));
        $apiVersion = (string) config('marketing.google_ads_api_version', 'v18');
        $url = "https://googleads.googleapis.com/{$apiVersion}/customers/{$customerId}/googleAds:search";

        $headers = [
            'Authorization' => 'Bearer '.$accessToken,
            'developer-token' => (string) config('marketing.google_ads_developer_token'),
            'Content-Type' => 'application/json',
        ];

        $loginCustomerId = config('marketing.google_ads_login_customer_id');
        if (is_string($loginCustomerId) && trim($loginCustomerId) !== '') {
            $headers['login-customer-id'] = $this->normalizeCustomerId($loginCustomerId);
        }

        $query = <<<'GAQL'
SELECT
  campaign.name,
  metrics.clicks,
  metrics.cost_micros,
  metrics.conversions
FROM campaign
WHERE segments.date DURING LAST_30_DAYS
  AND campaign.status != 'REMOVED'
ORDER BY metrics.clicks DESC
LIMIT 25
GAQL;

        $response = Http::timeout(25)
            ->withHeaders($headers)
            ->post($url, ['query' => $query]);

        if (! $response->successful()) {
            $message = $response->json('error.message')
                ?? $response->json('0.error.message')
                ?? mb_substr($response->body(), 0, 500);

            throw new \RuntimeException(is_string($message) ? $message : __('Google Ads API request failed.'));
        }

        $rows = $response->json('results') ?? [];
        $campaigns = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $name = data_get($row, 'campaign.name');
            $clicks = (int) data_get($row, 'metrics.clicks', 0);
            $costMicros = (int) data_get($row, 'metrics.costMicros', data_get($row, 'metrics.cost_micros', 0));
            $conversions = (float) data_get($row, 'metrics.conversions', 0);

            $campaigns[] = [
                'name' => is_string($name) && $name !== '' ? $name : __('Campaign'),
                'clicks' => $clicks,
                'cost' => round($costMicros / 1_000_000, 2),
                'conversions' => round($conversions, 2),
            ];
        }

        return $campaigns;
    }

    private function normalizeCustomerId(string $customerId): string
    {
        return preg_replace('/\D/', '', $customerId) ?? $customerId;
    }
}

<?php

namespace App\Services\Webhooks;

use App\Models\OutboundWebhook;
use App\Models\WebhookDelivery;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JsonException;
use Throwable;

class OutboundWebhookSender
{
    public function __construct(private readonly WebhookPayloadBuilder $payloadBuilder) {}

    /**
     * Deliver webhook with retries and per-attempt logs (PDF §6.4, §6.5).
     *
     * @param  array<string, mixed>  $payload
     */
    public function send(OutboundWebhook $webhook, string $eventKey, array $payload): void
    {
        if (! $webhook->is_enabled) {
            return;
        }

        $url = $webhook->target_url;
        if ($webhook->enforce_https && ! Str::startsWith(strtolower($url), 'https://')) {
            $this->logFailure($webhook, $eventKey, 1, null, null, false, 'HTTPS is required for this endpoint.', 0);

            return;
        }

        $method = strtoupper($webhook->http_method);
        $max = max(1, min(10, (int) $webhook->max_retries));
        $timeout = max(1, min(120, (int) $webhook->timeout_seconds));

        $lastError = 'Delivery failed.';

        for ($attempt = 1; $attempt <= $max; $attempt++) {
            $encodedBody = null;
            $started = microtime(true);

            try {
                if ($method === 'GET') {
                    $response = $this->sendGet($webhook, $eventKey, $payload, $timeout);
                } else {
                    $encodedBody = $this->payloadBuilder->bodyJson($webhook, $eventKey, $payload);
                    $response = $this->sendWithBody($webhook, $eventKey, $encodedBody, $method, $timeout);
                }

                $durationMs = (int) round((microtime(true) - $started) * 1000);
                $summary = $method === 'GET'
                    ? 'GET '.Str::limit($url, 500)
                    : Str::limit($method.' '.($encodedBody ?? ''), 4000);

                if ($response->successful()) {
                    $this->logSuccess($webhook, $eventKey, $attempt, $summary, $response, $durationMs);

                    return;
                }

                $lastError = 'HTTP '.$response->status();
                $this->logFailure(
                    $webhook,
                    $eventKey,
                    $attempt,
                    $summary,
                    $response,
                    false,
                    $lastError,
                    $durationMs
                );
            } catch (JsonException $e) {
                $durationMs = (int) round((microtime(true) - $started) * 1000);
                $lastError = 'Invalid payload template: '.$e->getMessage();
                $this->logFailure($webhook, $eventKey, $attempt, null, null, false, $lastError, $durationMs);

                return;
            } catch (Throwable $e) {
                $durationMs = (int) round((microtime(true) - $started) * 1000);
                $lastError = $e->getMessage();
                $this->logFailure($webhook, $eventKey, $attempt, null, null, false, $lastError, $durationMs);
            }

            if ($attempt < $max) {
                usleep(250_000);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendGet(OutboundWebhook $webhook, string $eventKey, array $payload, int $timeout): Response
    {
        $query = [
            'event' => $eventKey,
            'sent_at' => now()->toIso8601String(),
        ];

        $secret = $webhook->secret;
        if (is_string($secret) && $secret !== '') {
            $canonical = $eventKey.'|'.$query['sent_at'];
            $query['signature'] = hash_hmac('sha256', $canonical, $secret);
        }

        $pending = Http::timeout($timeout)
            ->withHeaders($this->baseHeaders($webhook, $eventKey, null));

        return $pending->get($webhook->target_url, $query);
    }

    private function sendWithBody(
        OutboundWebhook $webhook,
        string $eventKey,
        string $body,
        string $method,
        int $timeout
    ): Response {
        $headers = $this->baseHeaders($webhook, $eventKey, $body);

        $pending = Http::timeout($timeout)->withHeaders($headers);

        return match ($method) {
            'POST' => $pending->withBody($body, 'application/json')->post($webhook->target_url),
            'PUT' => $pending->withBody($body, 'application/json')->put($webhook->target_url),
            'PATCH' => $pending->withBody($body, 'application/json')->patch($webhook->target_url),
            default => $pending->withBody($body, 'application/json')->post($webhook->target_url),
        };
    }

    /**
     * @return array<string, string>
     */
    private function baseHeaders(OutboundWebhook $webhook, string $eventKey, ?string $jsonBody): array
    {
        $headers = array_merge(
            [
                'Accept' => 'application/json',
                'X-Webhook-Event' => $eventKey,
            ],
            $webhook->custom_headers ?? []
        );

        if ($jsonBody !== null) {
            $headers['Content-Type'] = 'application/json';
            $secret = $webhook->secret;
            if (is_string($secret) && $secret !== '') {
                $headers['X-Webhook-Signature'] = hash_hmac('sha256', $jsonBody, $secret);
            }
        }

        $token = $webhook->auth_bearer_token;
        if (is_string($token) && $token !== '') {
            $headers['Authorization'] = 'Bearer '.$token;
        }

        return $headers;
    }

    private function logSuccess(
        OutboundWebhook $webhook,
        string $eventKey,
        int $attempt,
        ?string $summary,
        Response $response,
        int $durationMs
    ): void {
        WebhookDelivery::query()->create([
            'outbound_webhook_id' => $webhook->id,
            'event_key' => $eventKey,
            'attempt_number' => $attempt,
            'request_summary' => $summary,
            'response_status' => $response->status(),
            'response_body' => $this->truncate($response->body(), 8000),
            'success' => true,
            'error_message' => null,
            'duration_ms' => $durationMs,
        ]);
    }

    private function logFailure(
        OutboundWebhook $webhook,
        string $eventKey,
        int $attempt,
        ?string $summary,
        ?Response $response,
        bool $httpOk,
        string $message,
        int $durationMs
    ): void {
        WebhookDelivery::query()->create([
            'outbound_webhook_id' => $webhook->id,
            'event_key' => $eventKey,
            'attempt_number' => $attempt,
            'request_summary' => $summary,
            'response_status' => $response?->status(),
            'response_body' => $response ? $this->truncate($response->body(), 8000) : null,
            'success' => $httpOk,
            'error_message' => Str::limit($message, 2000),
            'duration_ms' => $durationMs,
        ]);
    }

    private function truncate(string $value, int $max): string
    {
        if (strlen($value) <= $max) {
            return $value;
        }

        return substr($value, 0, $max).'…';
    }
}

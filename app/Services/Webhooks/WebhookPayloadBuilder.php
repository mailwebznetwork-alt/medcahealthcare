<?php

namespace App\Services\Webhooks;

use App\Models\OutboundWebhook;
use JsonException;

class WebhookPayloadBuilder
{
    /**
     * Build JSON request body for POST/PUT/PATCH requests.
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws JsonException
     */
    public function bodyJson(OutboundWebhook $webhook, string $eventKey, array $payload): string
    {
        $sentAt = now()->toIso8601String();
        $payloadJson = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $vars = [
            'event' => $eventKey,
            'sent_at' => $sentAt,
            'payload_json' => $payloadJson,
            'payload' => $payloadJson,
            'app_name' => (string) config('app.name'),
            'environment' => (string) config('app.env'),
        ];

        $template = $webhook->payload_template;
        if ($template === null || trim($template) === '') {
            return json_encode([
                'event' => $eventKey,
                'payload' => $payload,
                'sent_at' => $sentAt,
                'app' => $vars['app_name'],
                'environment' => $vars['environment'],
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }

        $replaced = $this->replacePlaceholders(trim($template), $vars);

        json_decode($replaced, true, 512, JSON_THROW_ON_ERROR);

        return $replaced;
    }

    /**
     * @param  array<string, string>  $vars
     */
    private function replacePlaceholders(string $template, array $vars): string
    {
        return (string) preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/',
            static function (array $matches) use ($vars): string {
                $key = $matches[1];

                return $vars[$key] ?? '';
            },
            $template
        );
    }
}

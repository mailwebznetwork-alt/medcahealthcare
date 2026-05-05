<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Services\ActivityLogService;
use App\Services\Integrations\GoogleService;
use App\Services\Integrations\MetaService;
use App\Services\Integrations\OpenAIService;
use App\Services\Integrations\StorageService;
use App\Services\Integrations\TwilioService;
use App\Services\Integrations\WebhookService;
use App\Services\Integrations\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IntegrationController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly GoogleService $googleService,
        private readonly MetaService $metaService,
        private readonly WhatsAppService $whatsAppService,
        private readonly TwilioService $twilioService,
        private readonly OpenAIService $openAIService,
        private readonly WebhookService $webhookService,
        private readonly StorageService $storageService
    ) {}

    public function index(): JsonResponse
    {
        $this->syncDefaults();

        $data = Integration::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Integration $integration): array => $this->toResponse($integration))
            ->values()
            ->all();

        return $this->ok('Integrations fetched successfully.', $data);
    }

    public function show(string $name): JsonResponse
    {
        $integration = $this->findByName($name);
        if (! $integration instanceof Integration) {
            return $this->error('Integration not found.', 404);
        }

        return $this->ok('Integration fetched successfully.', $this->toResponse($integration));
    }

    public function update(Request $request, string $name)
    {
        $integration = $this->findByName($name);
        if (! $integration instanceof Integration) {
            return $this->error('Integration not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'is_enabled' => ['sometimes', 'boolean'],
            'credentials' => ['required', 'array'],
            ...$this->rulesFor($name),
        ]);

        if ($validator->fails()) {
            if (! $request->expectsJson()) {
                return redirect()
                    ->route('settings.index')
                    ->withErrors($validator)
                    ->withInput();
            }

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'data' => $validator->errors(),
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $integration->forceFill([
                'credentials' => $validated['credentials'],
                'is_enabled' => (bool) ($validated['is_enabled'] ?? $integration->is_enabled),
            ])->save();

            $this->activityLogService->log(
                'integration_updated',
                'integrations',
                sprintf('Integration "%s" updated by user %d.', $integration->name, (int) auth()->id())
            );

            if (! $request->expectsJson()) {
                return redirect()
                    ->route('settings.index')
                    ->with('status', __('Integration ":name" updated successfully.', ['name' => $integration->name]));
            }

            return $this->ok('Integration updated successfully.', $this->toResponse($integration->fresh()));
        } catch (\Throwable $exception) {
            Log::error('Integration update failed.', ['name' => $name, 'error' => $exception->getMessage()]);
            $this->activityLogService->log('integration_update_failed', 'integrations', sprintf('Integration "%s" update failed.', $name));

            if (! $request->expectsJson()) {
                return redirect()
                    ->route('settings.index')
                    ->withErrors(['integration' => __('Integration update failed.')]);
            }

            return $this->error('Integration update failed.', 500);
        }
    }

    public function toggle(Request $request, string $name)
    {
        $integration = $this->findByName($name);
        if (! $integration instanceof Integration) {
            return $this->error('Integration not found.', 404);
        }

        try {
            $integration->forceFill(['is_enabled' => ! $integration->is_enabled])->save();

            $this->activityLogService->log(
                'integration_toggled',
                'integrations',
                sprintf('Integration "%s" set to %s by user %d.', $integration->name, $integration->is_enabled ? 'enabled' : 'disabled', (int) auth()->id())
            );

            if (! $request->expectsJson()) {
                return redirect()
                    ->route('settings.index')
                    ->with('status', __('Integration ":name" has been :state.', [
                        'name' => $integration->name,
                        'state' => $integration->is_enabled ? __('enabled') : __('disabled'),
                    ]));
            }

            return $this->ok('Integration toggled successfully.', [
                'name' => $integration->name,
                'is_enabled' => $integration->is_enabled,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Integration toggle failed.', ['name' => $name, 'error' => $exception->getMessage()]);
            $this->activityLogService->log('integration_toggle_failed', 'integrations', sprintf('Integration "%s" toggle failed.', $name));

            if (! $request->expectsJson()) {
                return redirect()
                    ->route('settings.index')
                    ->withErrors(['integration' => __('Integration toggle failed.')]);
            }

            return $this->error('Integration toggle failed.', 500);
        }
    }

    public function testConnection(Request $request, string $name)
    {
        $integration = $this->findByName($name);
        if (! $integration instanceof Integration) {
            return $this->error('Integration not found.', 404);
        }

        $result = match ($name) {
            'google_services' => $this->googleService->testConnection(),
            'meta_ads' => $this->metaService->testConnection(),
            'whatsapp_business' => $this->whatsAppService->testConnection(),
            'twilio' => $this->twilioService->testConnection(),
            'openai' => $this->openAIService->testConnection(),
            'webhook' => $this->webhookService->testConnection(),
            'aws_s3', 'cloudflare' => $this->storageService->testConnection(),
            default => ['success' => false, 'message' => 'Unsupported integration.', 'data' => []],
        };

        $this->activityLogService->log(
            $result['success'] ? 'integration_test_success' : 'integration_test_failure',
            'integrations',
            sprintf('Integration "%s" test result: %s.', $name, $result['message'])
        );

        if (! $request->expectsJson()) {
            if ($result['success']) {
                return redirect()
                    ->route('settings.index')
                    ->with('status', __('Integration ":name" test passed.', ['name' => $name]));
            }

            return redirect()
                ->route('settings.index')
                ->withErrors(['integration' => __('Integration ":name" test failed: :message', [
                    'name' => $name,
                    'message' => $result['message'],
                ])]);
        }

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    private function syncDefaults(): void
    {
        foreach ($this->definitions() as $name => $type) {
            Integration::query()->firstOrCreate(
                ['name' => $name],
                ['type' => $type, 'credentials' => [], 'is_enabled' => false]
            );
        }
    }

    private function findByName(string $name): ?Integration
    {
        if (! array_key_exists($name, $this->definitions())) {
            return null;
        }

        return Integration::query()->firstOrCreate(
            ['name' => $name],
            ['type' => $this->definitions()[$name], 'credentials' => [], 'is_enabled' => false]
        );
    }

    private function rulesFor(string $name): array
    {
        $base = [
            'credentials' => ['required', 'array'],
        ];

        $map = [
            'google_services' => [
                'credentials.measurement_id' => ['required', 'string', 'max:120'],
                'credentials.container_id' => ['required', 'string', 'max:120'],
                'credentials.verification_code' => ['required', 'string', 'max:255'],
                'credentials.location_id' => ['required', 'string', 'max:120'],
                'credentials.api_key' => ['required', 'string', 'max:255'],
            ],
            'meta_ads' => [
                'credentials.pixel_id' => ['required', 'string', 'max:120'],
                'credentials.access_token' => ['required', 'string', 'max:255'],
            ],
            'whatsapp_business' => [
                'credentials.phone_number_id' => ['required', 'string', 'max:120'],
                'credentials.access_token' => ['required', 'string', 'max:255'],
                'credentials.webhook_verify_token' => ['required', 'string', 'max:255'],
            ],
            'twilio' => [
                'credentials.sid' => ['required', 'string', 'max:120'],
                'credentials.auth_token' => ['required', 'string', 'max:255'],
                'credentials.from_number' => ['required', 'string', 'max:40'],
            ],
            'openai' => [
                'credentials.api_key' => ['required', 'string', 'max:255'],
                'credentials.model' => ['required', 'string', 'max:120'],
                'credentials.temperature' => ['required', 'numeric', 'min:0', 'max:2'],
            ],
            'webhook' => [
                'credentials.endpoint_url' => ['required', 'url', 'max:2048'],
                'credentials.secret' => ['required', 'string', 'max:255'],
            ],
            'aws_s3' => [
                'credentials.key' => ['required', 'string', 'max:255'],
                'credentials.secret' => ['required', 'string', 'max:255'],
                'credentials.region' => ['required', 'string', 'max:120'],
                'credentials.bucket' => ['required', 'string', 'max:255'],
            ],
            'cloudflare' => [
                'credentials.api_token' => ['required', 'string', 'max:255'],
                'credentials.zone_id' => ['required', 'string', 'max:255'],
            ],
        ];

        return array_merge($base, $map[$name] ?? []);
    }

    private function toResponse(Integration $integration): array
    {
        return [
            'id' => $integration->id,
            'name' => $integration->name,
            'type' => $integration->type,
            'is_enabled' => $integration->is_enabled,
            'last_used_at' => $integration->last_used_at?->toIso8601String(),
            'credentials' => $this->maskCredentials($integration->credentials),
            'updated_at' => $integration->updated_at?->toIso8601String(),
        ];
    }

    private function maskCredentials(array $credentials): array
    {
        $masked = [];
        $sensitiveKeys = [
            'api_key',
            'access_token',
            'auth_token',
            'secret',
            'webhook_verify_token',
            'sid',
            'key',
            'verification_code',
        ];

        foreach ($credentials as $key => $value) {
            if (is_array($value)) {
                $masked[$key] = $this->maskCredentials($value);

                continue;
            }

            if ($value === null || $value === '') {
                $masked[$key] = null;

                continue;
            }

            $stringValue = (string) $value;
            if (in_array((string) $key, $sensitiveKeys, true)) {
                $masked[$key] = str_repeat('*', max(0, mb_strlen($stringValue) - 4)).mb_substr($stringValue, -4);
            } else {
                $masked[$key] = $stringValue;
            }
        }

        return $masked;
    }

    private function definitions(): array
    {
        return [
            'google_services' => 'google',
            'meta_ads' => 'meta',
            'whatsapp_business' => 'whatsapp',
            'twilio' => 'communication',
            'openai' => 'ai',
            'webhook' => 'automation',
            'aws_s3' => 'storage',
            'cloudflare' => 'storage',
        ];
    }

    private function ok(string $message, array $data): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => [],
        ], $status);
    }
}

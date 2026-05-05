<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __invoke(): View
    {
        /** @var Collection<int, Integration> $integrations */
        $integrations = collect();

        if (Schema::hasTable('integrations')) {
            foreach ($this->definitions() as $name => $type) {
                Integration::query()->firstOrCreate(
                    ['name' => $name],
                    ['type' => $type, 'credentials' => [], 'is_enabled' => false]
                );
            }

            $integrations = Integration::query()
                ->orderBy('type')
                ->orderBy('name')
                ->get();
        }

        return view('settings.integrations', [
            'integrations' => $integrations,
            'fieldMap' => $this->fieldMap(),
        ]);
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

    private function fieldMap(): array
    {
        return [
            'google_services' => ['measurement_id', 'container_id', 'verification_code', 'location_id', 'api_key'],
            'meta_ads' => ['pixel_id', 'access_token'],
            'whatsapp_business' => ['phone_number_id', 'access_token', 'webhook_verify_token'],
            'twilio' => ['sid', 'auth_token', 'from_number'],
            'openai' => ['api_key', 'model', 'temperature'],
            'webhook' => ['endpoint_url', 'secret'],
            'aws_s3' => ['key', 'secret', 'region', 'bucket'],
            'cloudflare' => ['api_token', 'zone_id'],
        ];
    }
}

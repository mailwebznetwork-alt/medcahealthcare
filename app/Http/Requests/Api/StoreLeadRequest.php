<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $expected = config('security.lead_api_key');
        if (! is_string($expected) || trim($expected) === '') {
            abort(503, __('Lead ingest is not configured.'));
        }

        $provided = $this->header('X-API-KEY');
        if (! is_string($provided) || trim($provided) === '') {
            return false;
        }

        return hash_equals($expected, $provided);
    }

    protected function failedAuthorization(): void
    {
        throw new HttpException(401, __('Invalid or missing API key.'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'service' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
            'source' => ['nullable', 'string', 'max:64'],
            'campaign' => ['nullable', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:128'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'gclid' => ['nullable', 'string', 'max:255'],
            'fbclid' => ['nullable', 'string', 'max:255'],
            'landing_page' => ['nullable', 'string', 'max:500'],
            'referrer_url' => ['nullable', 'string', 'max:500'],
            'pin_code_id' => ['nullable', 'integer', 'exists:pin_codes,id'],
            'submission_context' => ['nullable', 'string', 'max:64'],
        ];
    }
}

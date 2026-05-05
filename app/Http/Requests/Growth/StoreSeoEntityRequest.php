<?php

namespace App\Http\Requests\Growth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSeoEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:2048'],
            'og_image_url' => ['nullable', 'string', 'max:2048'],
            'same_as' => ['nullable', 'array'],
            'same_as.*' => ['nullable', 'url', 'max:2048'],
            'same_as_json' => ['nullable', 'string', 'json'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'phone_e164' => ['nullable', 'string', 'max:32'],
            'country_code' => ['nullable', 'string', 'max:8'],
            'street_address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:64'],
            'postal_code' => ['nullable', 'string', 'max:32'],
            'google_place_id' => ['nullable', 'string', 'max:256'],
            'google_business_profile_url' => ['nullable', 'url', 'max:2048'],
            'has_map_url' => ['nullable', 'url', 'max:2048'],
            'entity_faqs' => ['nullable', 'array', 'max:40'],
            'entity_faqs.*.question' => ['required', 'string', 'max:500'],
            'entity_faqs.*.answer' => ['required', 'string', 'max:8000'],
            'entity_faqs_json' => ['nullable', 'string'],
            'custom_json_ld_raw' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $raw = $this->input('custom_json_ld_raw');
            if (! is_string($raw) || trim($raw) === '') {
                return;
            }

            json_decode($raw);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $validator->errors()->add('custom_json_ld_raw', __('Must be valid JSON.'));

                return;
            }

            $decoded = json_decode($raw, true);
            if (! is_array($decoded)) {
                $validator->errors()->add('custom_json_ld_raw', __('JSON-LD must be a JSON array or object.'));
            }
        });

        $validator->after(function (Validator $validator): void {
            $raw = $this->input('entity_faqs_json');
            if (! is_string($raw) || trim($raw) === '') {
                return;
            }

            json_decode($raw);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $validator->errors()->add('entity_faqs_json', __('Must be valid JSON.'));

                return;
            }

            $decoded = json_decode($raw, true);
            if (! is_array($decoded)) {
                $validator->errors()->add('entity_faqs_json', __('FAQs must be a JSON array.'));

                return;
            }

            if (count($decoded) > 40) {
                $validator->errors()->add('entity_faqs_json', __('A maximum of 40 FAQ items is allowed.'));

                return;
            }

            foreach ($decoded as $index => $row) {
                if (! is_array($row)) {
                    $validator->errors()->add('entity_faqs_json', __('Each FAQ must be an object with question and answer.'));

                    return;
                }
                if (! isset($row['question'], $row['answer']) || ! is_string($row['question']) || ! is_string($row['answer'])) {
                    $validator->errors()->add('entity_faqs_json', __('Each FAQ needs string question and answer keys.'));

                    return;
                }
                if (strlen($row['question']) > 500 || strlen($row['answer']) > 8000) {
                    $validator->errors()->add('entity_faqs_json', __('FAQ question or answer exceeds maximum length.'));

                    return;
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('same_as_json')) {
            return;
        }

        $raw = $this->input('same_as_json');
        if (! is_string($raw) || trim($raw) === '') {
            return;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return;
        }

        $urls = [];
        foreach ($decoded as $item) {
            if (is_string($item) && filter_var($item, FILTER_VALIDATE_URL)) {
                $urls[] = $item;
            }
        }

        $this->merge(['same_as' => $urls]);

        if (! $this->has('entity_faqs_json')) {
            return;
        }

        $faqRaw = $this->input('entity_faqs_json');
        if (! is_string($faqRaw) || trim($faqRaw) === '') {
            $this->merge(['entity_faqs' => []]);

            return;
        }

        $faqDecoded = json_decode($faqRaw, true);
        if (! is_array($faqDecoded)) {
            return;
        }

        $normalized = [];
        foreach ($faqDecoded as $row) {
            if (! is_array($row) || ! isset($row['question'], $row['answer'])) {
                continue;
            }
            if (! is_string($row['question']) || ! is_string($row['answer'])) {
                continue;
            }
            $normalized[] = [
                'question' => $row['question'],
                'answer' => $row['answer'],
            ];
        }

        $this->merge(['entity_faqs' => $normalized]);
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array<string, mixed> $data */
        $data = parent::validated($key, $default);

        if (isset($data['custom_json_ld_raw']) && is_string($data['custom_json_ld_raw']) && trim($data['custom_json_ld_raw']) !== '') {
            $data['custom_json_ld'] = json_decode($data['custom_json_ld_raw'], true);
        } else {
            $data['custom_json_ld'] = null;
        }

        unset($data['custom_json_ld_raw'], $data['same_as_json'], $data['entity_faqs_json']);

        if (isset($data['entity_faqs']) && is_array($data['entity_faqs']) && $data['entity_faqs'] === []) {
            $data['entity_faqs'] = null;
        }

        return $data;
    }
}

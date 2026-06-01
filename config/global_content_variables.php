<?php

/**
 * Global content variable definitions (Patch 1).
 * Values stored in global_content_variables table; defaults fall back to theme branding / medca config.
 */
return [

    'keys' => [
        'company_name' => [
            'label' => 'Company Name',
            'branding_key' => 'brand_name',
            'medca_key' => 'brand_name',
        ],
        'tagline' => [
            'label' => 'Tagline',
            'branding_key' => 'tagline',
            'medca_key' => 'tagline',
        ],
        'phone_number' => [
            'label' => 'Phone Number',
            'branding_key' => 'phone_display',
            'medca_key' => 'phone_display',
        ],
        'phone_tel' => [
            'label' => 'Phone (tel link)',
            'branding_key' => 'phone_tel',
            'medca_key' => 'phone_tel',
        ],
        'whatsapp' => [
            'label' => 'WhatsApp Number / URL',
            'branding_key' => 'whatsapp_url',
            'medca_key' => 'whatsapp_url',
        ],
        'email' => [
            'label' => 'Email',
            'branding_key' => 'contact_email',
            'medca_key' => 'contact_email',
        ],
        'address' => [
            'label' => 'Address',
            'branding_key' => 'address',
            'medca_key' => 'location_display',
        ],
        'primary_cta' => [
            'label' => 'Primary CTA',
            'branding_key' => 'primary_cta_text',
            'medca_key' => 'primary_cta_text',
        ],
        'secondary_cta' => [
            'label' => 'Secondary CTA',
            'branding_key' => 'secondary_cta_text',
            'medca_key' => null,
        ],
        'business_hours' => [
            'label' => 'Business Hours',
            'branding_key' => 'business_hours',
            'medca_key' => null,
        ],
        'website_url' => [
            'label' => 'Website URL',
            'branding_key' => 'brand_url',
            'medca_key' => null,
        ],
    ],

];

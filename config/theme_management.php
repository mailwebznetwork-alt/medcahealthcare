<?php

return [

    'cache_key' => 'theme.configuration.published',

    'cache_ttl_seconds' => 3600,

    /** Google Fonts families allowed in Appearance → Typography */
    'font_whitelist' => [
        'Plus Jakarta Sans',
        'Noto Sans',
        'Inter',
        'Roboto',
        'Open Sans',
        'Lato',
        'Merriweather',
    ],

    'header_presets' => [
        'classic_healthcare' => [
            'label' => 'Classic Healthcare',
            'description' => 'Navy top strip + white brand row (current Medca default).',
            'class' => 'medca-header-classic',
        ],
        'corporate' => [
            'label' => 'Corporate',
            'description' => 'Flat white header with subtle shadow.',
            'class' => 'medca-header-corporate',
        ],
        'premium' => [
            'label' => 'Premium',
            'description' => 'Dark brand row with gold accent underline.',
            'class' => 'medca-header-premium',
        ],
        'minimal' => [
            'label' => 'Minimal',
            'description' => 'Single-row compact navigation.',
            'class' => 'medca-header-minimal',
        ],
        'modern' => [
            'label' => 'Modern',
            'description' => 'Rounded container header with soft border.',
            'class' => 'medca-header-modern',
        ],
    ],

    'layout_presets' => [
        'contained' => [
            'label' => 'Contained',
            'main_class' => 'mx-auto w-full max-w-6xl px-4 md:px-6 lg:px-8',
            'shell_class' => 'max-w-6xl',
        ],
        'wide' => [
            'label' => 'Wide',
            'main_class' => 'mx-auto w-full max-w-7xl px-4 md:px-6 lg:px-8',
            'shell_class' => 'max-w-7xl',
        ],
        'full' => [
            'label' => 'Full Width',
            'main_class' => 'mx-auto w-full max-w-full px-4 md:px-6',
            'shell_class' => 'max-w-full',
        ],
    ],

    'branding_fields' => [
        'brand_name',
        'tagline',
        'company_legal_name',
        'phone_display',
        'phone_tel',
        'whatsapp_url',
        'contact_email',
        'brand_url',
        'primary_cta_text',
        'logo_path',
        'favicon_path',
    ],

];

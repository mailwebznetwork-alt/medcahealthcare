<?php

return [

    'premium_healthcare_intro' => [
        'name' => 'Premium Healthcare Intro',
        'description' => 'Hero + statistics-style services overview + CTA.',
        'style_pack_slug' => 'healthcare_premium',
        'blocks_json' => [
            ['slug' => 'hero-home', 'style_variant' => 'style_2'],
            ['slug' => 'services-overview-home', 'style_variant' => 'style_2'],
            ['slug' => 'cta-home', 'style_variant' => 'style_2'],
        ],
    ],

    'trust_builder' => [
        'name' => 'Trust Builder',
        'description' => 'Services grid + CTA for conversion.',
        'style_pack_slug' => 'healthcare_professional',
        'blocks_json' => [
            ['slug' => 'services-grid-full', 'style_variant' => 'style_1'],
            ['slug' => 'cta-services', 'style_variant' => 'style_1'],
        ],
    ],

];

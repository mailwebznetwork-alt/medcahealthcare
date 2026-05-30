<?php

/**
 * Component Standardization registry (Phase 5).
 *
 * Canonical Blade components for each UI shell. Prefer these over raw CSS class strings.
 */
return [

    'admin' => [
        'workspace' => 'admin.workspace',
        'card' => 'admin.card',
        'button_primary' => 'primary-button',
        'button_secondary' => 'secondary-button',
        'link_button' => 'admin.link-button',
        'css' => [
            'card' => 'mom-card',
            'cta_primary' => 'mom-cta-primary',
            'cta_ghost' => 'mom-cta-ghost',
            'cta_compact' => 'mom-cta-compact',
        ],
    ],

    'public' => [
        'card' => 'public.card',
        'section' => 'public.section',
        'hero' => 'public.hero',
        'content_shell' => 'public.content-shell',
        'button_primary' => 'primary-button',
        'css' => [
            'card' => 'service-card',
            'cta_primary' => 'btn-premium',
        ],
    ],

    'auth' => [
        'button_primary' => 'primary-button',
        'button_secondary' => 'secondary-button',
    ],

];

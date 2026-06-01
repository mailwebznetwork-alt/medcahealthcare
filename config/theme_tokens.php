<?php

/**
 * Public theme token catalog (Phase 8.5 expansion).
 * Colors use existing ThemeConfigRepository path; shape tokens use published_shape JSON.
 */
return [

    'colors' => [
        'primary' => ['css' => '--medca-primary', 'default' => '#0055ff', 'type' => 'color'],
        'primary_hover' => ['css' => '--medca-primary-hover', 'default' => '#001e5c', 'type' => 'color'],
        'navy' => ['css' => '--medca-navy', 'default' => '#001f5c', 'type' => 'color'],
        'navy_mid' => ['css' => '--medca-navy-mid', 'default' => '#012a7d', 'type' => 'color'],
        'navy_border' => ['css' => '--medca-navy-border', 'default' => '#001433', 'type' => 'color'],
        'navy_accent' => ['css' => '--medca-navy-accent', 'default' => '#164081', 'type' => 'color'],
        'text_primary' => ['css' => '--medca-text-primary', 'default' => '#0f172a', 'type' => 'color'],
        'text_secondary' => ['css' => '--medca-text-secondary', 'default' => '#475569', 'type' => 'color'],
        'text_muted' => ['css' => '--medca-text-muted', 'default' => '#64748b', 'type' => 'color'],
        'surface' => ['css' => '--medca-surface', 'default' => '#ffffff', 'type' => 'color'],
        'surface_muted' => ['css' => '--medca-surface-muted', 'default' => '#f8fafc', 'type' => 'color'],
        'surface_elevated' => ['css' => '--medca-surface-elevated', 'default' => '#f1f5f9', 'type' => 'color'],
        'border' => ['css' => '--medca-border', 'default' => '#e2e8f0', 'type' => 'color'],
        'success' => ['css' => '--medca-success', 'default' => '#16a34a', 'type' => 'color'],
        'warning' => ['css' => '--medca-warning', 'default' => '#d97706', 'type' => 'color'],
        'danger' => ['css' => '--medca-danger', 'default' => '#dc2626', 'type' => 'color'],
    ],

    'radius' => [
        'radius_sm' => ['css' => '--medca-radius-sm', 'default' => '8px', 'type' => 'length'],
        'radius_md' => ['css' => '--medca-radius-md', 'default' => '12px', 'type' => 'length'],
        'radius_lg' => ['css' => '--medca-radius-lg', 'default' => '16px', 'type' => 'length'],
        'radius_xl' => ['css' => '--medca-radius-xl', 'default' => '24px', 'type' => 'length'],
        'button_radius' => ['css' => '--medca-button-radius', 'default' => '8px', 'type' => 'length'],
        'card_radius' => ['css' => '--medca-card-radius', 'default' => '12px', 'type' => 'length'],
    ],

    'shadow' => [
        'shadow_surface' => ['css' => '--medca-shadow-surface', 'default' => '0 10px 30px rgba(10, 25, 47, 0.08)', 'type' => 'shadow'],
        'shadow_elevated' => ['css' => '--medca-shadow-elevated', 'default' => '0 20px 45px rgba(10, 25, 47, 0.12)', 'type' => 'shadow'],
        'shadow_hover' => ['css' => '--medca-shadow-hover', 'default' => '0 24px 50px rgba(10, 25, 47, 0.16)', 'type' => 'shadow'],
        'card_shadow' => ['css' => '--medca-card-shadow', 'default' => '0 10px 30px rgba(10, 25, 47, 0.08)', 'type' => 'shadow'],
    ],

    'spacing' => [
        'spacing_section_y' => ['css' => '--medca-spacing-section-y', 'default' => '4rem', 'type' => 'length'],
        'spacing_block_gap' => ['css' => '--medca-spacing-block-gap', 'default' => '2rem', 'type' => 'length'],
        'container_padding_x' => ['css' => '--medca-container-padding-x', 'default' => '1.5rem', 'type' => 'length'],
    ],

    'layout' => [
        'container_max' => ['css' => '--medca-container-max', 'default' => '72rem', 'type' => 'length'],
    ],

    'carousel' => [
        'carousel_gap' => ['css' => '--medca-carousel-gap', 'default' => '1.5rem', 'type' => 'length'],
        'carousel_nav_size' => ['css' => '--medca-carousel-nav-size', 'default' => '2.5rem', 'type' => 'length'],
    ],

];

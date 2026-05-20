<?php

use App\Enums\PageLayoutMode;
use App\Models\Block;
use App\Models\Page;
use App\Services\ContentParser;

it('wraps block output with a style tag when custom_css is set', function () {
    Block::query()->updateOrCreate(
        ['block_slug' => 'css-demo-block'],
        [
            'block_name' => 'CSS demo',
            'code' => '<p class="css-demo-target">Hello</p>',
            'custom_css' => '.css-demo-target { color: rgb(1, 2, 3); }',
            'is_active' => true,
        ]
    );

    Page::query()->updateOrCreate(
        ['slug' => 'css-demo-page'],
        [
            'title' => 'CSS demo page',
            'content' => '{{block:css-demo-block}}',
            'is_active' => true,
            'layout_mode' => PageLayoutMode::Canvas,
        ]
    );

    $html = ContentParser::parse('{{block:css-demo-block}}');

    expect($html)
        ->toContain('<style data-block="css-demo-block" type="text/css">')
        ->toContain('.css-demo-target { color: rgb(1, 2, 3); }')
        ->toContain('<p class="css-demo-target">Hello</p>');
});

it('strips accidental style tags from custom_css input', function () {
    $normalized = ContentParser::normalizeBlockCustomCss(
        '<style>.x { margin: 0; }</style>'
    );

    expect($normalized)->toBe('.x { margin: 0; }');
});

it('serves block custom css on a public page', function () {
    Block::query()->updateOrCreate(
        ['block_slug' => 'public-css-block'],
        [
            'block_name' => 'Public CSS',
            'code' => '<span id="public-css-marker">ok</span>',
            'custom_css' => '#public-css-marker { font-weight: 700; }',
            'is_active' => true,
        ]
    );

    Page::query()->updateOrCreate(
        ['slug' => 'public-css-page'],
        [
            'title' => 'Public CSS page',
            'content' => '{{block:public-css-block}}',
            'is_active' => true,
            'layout_mode' => PageLayoutMode::Canvas,
        ]
    );

    $this->get('/p/public-css-page')
        ->assertSuccessful()
        ->assertSee('data-block="public-css-block"', false)
        ->assertSee('#public-css-marker { font-weight: 700; }', false)
        ->assertSee('public-css-marker', false);
});

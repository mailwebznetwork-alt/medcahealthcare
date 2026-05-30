<?php

use App\Services\Growth\PublicUrlNormalizer;

it('rewrites legacy test hosts to the configured app url host', function () {
    config(['app.url' => 'https://medcaeducation.in']);

    $normalizer = app(PublicUrlNormalizer::class);

    expect($normalizer->normalizeUrl('http://markonmindsplus.test/p/about-us'))
        ->toBe('https://medcaeducation.in/p/about-us')
        ->and($normalizer->normalizeUrl('https://markonminds.test/services'))
        ->toBe('https://medcaeducation.in/services');
});

it('normalizes hreflang json values and robots sitemap lines', function () {
    config(['app.url' => 'https://medcaeducation.in']);

    $normalizer = app(PublicUrlNormalizer::class);

    $hreflang = $normalizer->normalizeHreflang([
        'en' => 'http://markonmindsplus.test/about-us',
        'hi' => 'https://medcaeducation.in/about-us',
    ]);

    expect($hreflang['en'])->toBe('https://medcaeducation.in/about-us')
        ->and($hreflang['hi'])->toBe('https://medcaeducation.in/about-us');

    $robots = $normalizer->normalizeRobotsTxt("User-agent: *\nAllow: /\nSitemap: http://markonmindsplus.test/sitemap.xml");

    expect($robots)->toContain('Sitemap: https://medcaeducation.in/sitemap.xml');
});

it('command dry run reports business profile website rewrites', function () {
    config(['app.url' => 'https://medcaeducation.in']);

    if (! \Illuminate\Support\Facades\Schema::hasTable('business_profiles')) {
        $this->markTestSkipped('business_profiles table missing.');
    }

    \App\Models\BusinessProfile::query()->create([
        'name' => 'Medca Health Care',
        'email' => 'hello@medca.test',
        'website' => 'http://markonmindsplus.test',
    ]);

    $this->artisan('medca:normalize-site-urls', ['--dry-run' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('http://markonmindsplus.test')
        ->expectsOutputToContain('https://medcaeducation.in');
});

<?php

use App\Models\SiteNavigationItem;
use Database\Seeders\MedcaPublicPagesSeeder;

beforeEach(function (): void {
    $this->seed(MedcaPublicPagesSeeder::class);
});

it('renders the same primary header navigation on home, cms, and catalog routes', function () {
    $paths = ['/', '/about-us', '/services', '/services-catalog'];

    $baseline = null;

    foreach ($paths as $path) {
        $html = $this->get($path)->assertSuccessful()->getContent();
        expect($html)->toContain(config('medca.brand_name'));

        preg_match('/<nav class="flex shrink-0 items-center justify-end" aria-label="Primary">.*?<\/nav>/s', $html, $matches);
        expect($matches[0] ?? '')->not->toBe('');

        expect($matches[0])->toContain('flex-nowrap')
            ->toContain('text-[14px]')
            ->toContain('font-semibold')
            ->toContain('tracking-[0.5px]')
            ->toContain('whitespace-nowrap');

        preg_match_all('/href="([^"]+)"[^>]*>\s*([^<]+)\s*<\/a>/', $matches[0], $linkMatches, PREG_SET_ORDER);
        $links = array_map(
            static fn (array $row): array => ['href' => $row[1], 'label' => trim($row[2])],
            $linkMatches
        );

        if ($baseline === null) {
            $baseline = $links;
        } else {
            expect($links)->toBe($baseline);
        }
    }
});

it('uses cms page urls in the default header when site navigation is not configured', function () {
    SiteNavigationItem::query()->delete();

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('href="'.url('/about-us').'"', false)
        ->assertSee('href="'.url('/services').'"', false)
        ->assertDontSee('href="'.url('/#about').'"', false);
});

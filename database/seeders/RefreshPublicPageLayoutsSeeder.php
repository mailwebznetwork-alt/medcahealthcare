<?php

namespace Database\Seeders;

use App\Enums\PageLayoutMode;
use App\Models\Block;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Strips system-imposed service/careers layouts so blocks hold data tokens
 * and minimal markup for Site Architect styling.
 */
class RefreshPublicPageLayoutsSeeder extends Seeder
{
    public function run(): void
    {
        Block::query()->where('block_slug', 'sdfdfsdf')->update([
            'code' => "{{service:homenursing-services}}\n{{service:caregivers}}",
        ]);

        // Do not overwrite block slug "careers" or the careers page — admins own that layout in Site Architect.

        $cta = Block::query()->where('block_slug', 'cta-services')->first();
        if ($cta !== null) {
            $cta->code = str_replace('/p/contact', '/contact', (string) $cta->code);
            $cta->save();
        }

        // careers-job-detail block + page: owned in Site Architect — never overwrite here.

        Block::query()->updateOrCreate(
            ['block_slug' => 'services-block-carousel'],
            [
                'block_name' => 'Services — carousel (pick services)',
                'code' => <<<'BLADE'
{{-- Site Architect → Insert service (adds {{service:code}} tokens). $services = only those codes. --}}
{{service:caregivers}}
{{service:homenursing-services}}
@include('public.services.partials.services-carousel', [
    'services' => $services,
    'sectionTitle' => __('Our clinical services'),
])
BLADE,
                'is_active' => true,
            ]
        );

        Block::query()->updateOrCreate(
            ['block_slug' => 'services-block-grid'],
            [
                'block_name' => 'Services — grid (pick services)',
                'code' => <<<'BLADE'
{{-- Add {{service:your-code}} lines for each service in this category. --}}
@include('public.services.partials.services-grid', [
    'services' => $services,
    'sectionTitle' => __('All services'),
])
BLADE,
                'is_active' => true,
            ]
        );

        Block::query()->updateOrCreate(
            ['block_slug' => 'service-detail-hero'],
            [
                'block_name' => 'Service detail — hero (uses $service)',
                'code' => <<<'BLADE'
<section class="w-full" data-service-detail-hero>
    <header>
        <h1>{{ $service->seo?->h1 ?: $service->title }}</h1>
        @if (filled($service->short_summary))
            <p>{{ $service->short_summary }}</p>
        @endif
    </header>
    @if (filled($service->description))
        <div>{!! $service->description !!}</div>
    @endif
</section>
BLADE,
                'is_active' => true,
            ]
        );

        Block::query()->updateOrCreate(
            ['block_slug' => 'service-detail-related'],
            [
                'block_name' => 'Service detail — related (Insert service tokens)',
                'code' => <<<'BLADE'
{{-- Insert service → adds {{service:code}} lines above. Carousel hidden until at least one token exists. --}}
@include('public.services.partials.services-carousel', [
    'services' => $services,
    'sectionTitle' => __('Related services'),
])
BLADE,
                'is_active' => true,
            ]
        );

        Block::query()->updateOrCreate(
            ['block_slug' => 'services-detail-layout'],
            [
                'block_name' => 'Services — detail fallback',
                'code' => <<<'BLADE'
{{block:service-detail-hero}}
{{block:service-detail-related}}
BLADE,
                'is_active' => true,
            ]
        );

        Page::query()->updateOrCreate(
            ['slug' => 'services-detail-template'],
            [
                'title' => 'Service detail (shared layout)',
                'content' => '{{block:services-detail-layout}}',
                'is_active' => true,
                'layout_mode' => PageLayoutMode::Canvas,
            ]
        );

        Page::query()->where('slug', 'services')->update([
            'content' => "{{block:hero-services}}\n{{block:services-block-carousel}}\n{{block:cta-services}}",
            'layout_mode' => PageLayoutMode::Canvas,
        ]);
    }
}

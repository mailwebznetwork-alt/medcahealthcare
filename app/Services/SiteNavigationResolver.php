<?php

namespace App\Services;

use App\Models\Page;
use App\Models\SiteNavigationItem;
use Illuminate\Support\Facades\Schema;

class SiteNavigationResolver
{
    /**
     * @return list<array{label: string, href: string}>
     */
    public function headerLinks(): array
    {
        return once(function (): array {
            if (! Schema::hasTable('site_navigation_items')) {
                return $this->defaultHeaderLinks();
            }

            $rows = SiteNavigationItem::query()
                ->where('zone', SiteNavigationItem::ZONE_HEADER)
                ->orderBy('sort_order')
                ->with('page')
                ->get();

            $links = [];
            foreach ($rows as $row) {
                $page = $row->page;
                if ($page === null || ! $page->is_active) {
                    continue;
                }
                $links[] = [
                    'label' => $this->resolveNavLabel($row, $page),
                    'href' => $this->resolveHref($page),
                ];
            }

            if ($links === []) {
                return $this->defaultHeaderLinks();
            }

            return $links;
        });
    }

    /**
     * @return list<array{label: string, href: string}>
     */
    public function footerLinks(): array
    {
        return once(function (): array {
            if (! Schema::hasTable('site_navigation_items')) {
                return [];
            }

            $rows = SiteNavigationItem::query()
                ->where('zone', SiteNavigationItem::ZONE_FOOTER)
                ->orderBy('sort_order')
                ->with('page')
                ->get();

            $links = [];
            foreach ($rows as $row) {
                $page = $row->page;
                if ($page === null || ! $page->is_active) {
                    continue;
                }
                $links[] = [
                    'label' => $this->resolveNavLabel($row, $page),
                    'href' => $this->resolveHref($page),
                ];
            }

            return $links;
        });
    }

    /**
     * Pages with the reserved slug 'home' resolve to '/' so the marketing root URL stays canonical.
     */
    protected function resolveHref(Page $page): string
    {
        if ($page->slug === 'home') {
            return url('/');
        }

        return $page->publicUrl();
    }

    protected function resolveNavLabel(SiteNavigationItem $row, Page $page): string
    {
        $custom = $row->custom_label ?? null;

        if ($custom !== null && trim((string) $custom) !== '') {
            return trim((string) $custom);
        }

        return $page->title;
    }

    /**
     * @return list<array{label: string, href: string}>
     */
    protected function defaultHeaderLinks(): array
    {
        /** @var array<string, string> $slugLabels */
        $slugLabels = config('public_pages.default_header_nav', []);

        if ($slugLabels !== [] && Schema::hasTable('pages')) {
            $links = [];

            foreach ($slugLabels as $slug => $label) {
                $href = $this->defaultHrefForSlug((string) $slug);
                if ($href === null) {
                    continue;
                }

                $links[] = [
                    'label' => __($label),
                    'href' => $href,
                ];
            }

            if ($links !== []) {
                return $links;
            }
        }

        return $this->staticFallbackHeaderLinks();
    }

    protected function defaultHrefForSlug(string $slug): ?string
    {
        if (Schema::hasTable('pages')) {
            $page = Page::query()
                ->where('slug', $slug)
                ->where('is_active', true)
                ->first();

            if ($page instanceof Page) {
                return $this->resolveHref($page);
            }
        }

        return match ($slug) {
            'home' => url('/'),
            'about-us' => url('/about-us'),
            'services' => url('/services'),
            'locations' => url('/locations'),
            'careers' => url('/careers'),
            'contact' => url('/contact'),
            default => null,
        };
    }

    /**
     * @return list<array{label: string, href: string}>
     */
    protected function staticFallbackHeaderLinks(): array
    {
        return [
            ['label' => __('Home'), 'href' => url('/')],
            ['label' => __('About Us'), 'href' => url('/about-us')],
            ['label' => __('Services'), 'href' => url('/services')],
            ['label' => __('Locations'), 'href' => url('/locations')],
            ['label' => __('Careers'), 'href' => url('/careers')],
            ['label' => __('Contact Us'), 'href' => url('/contact')],
        ];
    }
}

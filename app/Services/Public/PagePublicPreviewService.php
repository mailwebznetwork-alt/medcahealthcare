<?php

namespace App\Services\Public;

use App\Models\Page;
use App\Models\Service;
use App\Services\Content\ContentRenderContext;

class PagePublicPreviewService
{
    public function __construct(
        private readonly PublicPagePresenter $presenter,
        private readonly ContentRenderContext $renderContext,
    ) {}

    /**
     * View data for Site Architect page preview (matches public rendering context).
     *
     * @return array{page: Page, service?: Service}
     */
    public function viewDataFor(Page $page): array
    {
        $page->loadMissing('faqs');

        $service = $this->resolveServiceForPage($page);

        if ($service !== null) {
            $this->renderContext->set($this->presenter->variablesForServiceDetail($service));

            return [
                'page' => $page,
                'service' => $service,
            ];
        }

        $this->renderContext->set($this->presenter->variablesFor($page));

        return ['page' => $page];
    }

    private function resolveServiceForPage(Page $page): ?Service
    {
        $linked = Service::query()
            ->where('detail_page_id', $page->id)
            ->first();

        if ($linked !== null) {
            return $linked;
        }

        $code = $this->serviceCodeFromDetailPageSlug($page->slug);

        if ($code === null) {
            return null;
        }

        return Service::query()->where('service_code', $code)->first();
    }

    private function serviceCodeFromDetailPageSlug(string $slug): ?string
    {
        $pattern = (string) config('public_pages.service_detail_page_slug_pattern', 'service-{code}');
        $prefix = str_replace('{code}', '', $pattern);

        if ($prefix === '' || ! str_starts_with($slug, $prefix)) {
            return null;
        }

        $code = substr($slug, strlen($prefix));

        return $code !== '' ? $code : null;
    }
}

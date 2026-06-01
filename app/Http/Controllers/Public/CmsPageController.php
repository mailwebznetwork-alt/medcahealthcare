<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\Content\ContentRenderContext;
use App\Services\Public\PublicPagePresenter;
use Illuminate\View\View;

class CmsPageController extends Controller
{
    public function __construct(
        private readonly PublicPagePresenter $presenter,
        private readonly ContentRenderContext $renderContext,
    ) {}

    public function show(string $slug): View
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $this->renderContext->set(array_merge(
            $this->presenter->variablesFor($page),
            app(\App\Services\Deployment\StylePackResolver::class)->contextVariables($page),
            ['currentPage' => $page],
        ));

        return view('layouts.app', ['page' => $page]);
    }
}

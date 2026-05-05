<?php

namespace App\Http\Controllers\Growth;

use App\Http\Controllers\Controller;
use App\Models\Competitor;
use Illuminate\View\View;

class CompetitorPageController extends Controller
{
    public function __invoke(): View
    {
        $competitors = Competitor::query()
            ->withCount(['keywords', 'leads'])
            ->orderByDesc('is_intercept_target')
            ->orderBy('name')
            ->paginate(20);

        return view('growth-center.competitors.index', [
            'competitors' => $competitors,
        ]);
    }
}

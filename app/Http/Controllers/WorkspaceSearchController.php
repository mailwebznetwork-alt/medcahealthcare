<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ModuleAccess;
use App\Services\WorkspaceGlobalSearch;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class WorkspaceSearchController extends Controller
{
    public function __invoke(Request $request, WorkspaceGlobalSearch $search): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $user = $request->user();
        abort_if($user === null, 403);

        $payload = $search->search((string) ($validated['q'] ?? ''));

        $allowedHeadings = ['Hub navigation'];

        if ($user->hasModuleAccess(ModuleAccess::SITE_ARCHITECT)) {
            $allowedHeadings = array_merge($allowedHeadings, ['Pages', 'Blogs', 'Blocks']);
        }
        if ($user->hasModuleAccess(ModuleAccess::OPERATIONS)) {
            $allowedHeadings = array_merge($allowedHeadings, [
                'Services',
                'PIN codes',
                'Bookings / leads',
                'Job vacancies',
            ]);
        }
        if ($user->hasModuleAccess(ModuleAccess::USER_MANAGEMENT)) {
            $allowedHeadings[] = 'People';
        }
        if ($user->hasModuleAccess(ModuleAccess::GROWTH_CENTER)) {
            $allowedHeadings[] = 'Competitors';
        }

        $allowedHeadings = array_values(array_unique($allowedHeadings));

        $payload['groups'] = array_values(array_filter(
            $payload['groups'],
            static fn (array $group): bool => in_array((string) ($group['heading'] ?? ''), $allowedHeadings, true)
        ));

        return view('workspace.search', $payload);
    }
}

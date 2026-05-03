<?php

namespace App\Http\Controllers\Operations\JobPortal;

use App\Enums\ApplicationPipelineStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Application::class);

        $query = Application::query()->with('vacancy')->orderByDesc('created_at');

        if ($request->filled('vacancy_id')) {
            $query->where('vacancy_id', $request->integer('vacancy_id'));
        }

        if ($request->filled('pipeline_status')) {
            $query->where('pipeline_status', $request->string('pipeline_status'));
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim().'%';
            $query->where(function ($q) use ($term): void {
                $q->where('full_name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        $applications = $query->paginate(20)->withQueryString();
        $vacancies = Vacancy::query()->orderBy('title')->get(['id', 'title']);

        return view('operations.job-portal.applications.index', compact('applications', 'vacancies'));
    }

    public function show(Application $application): View
    {
        $this->authorize('view', $application);
        $application->load('vacancy');

        return view('operations.job-portal.applications.show', compact('application'));
    }

    public function update(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'pipeline_status' => ['required', Rule::enum(ApplicationPipelineStatus::class)],
        ]);

        $application->update($validated);

        return redirect()
            ->route('operations.job-portal.applications.show', $application)
            ->with('status', 'application-updated');
    }
}

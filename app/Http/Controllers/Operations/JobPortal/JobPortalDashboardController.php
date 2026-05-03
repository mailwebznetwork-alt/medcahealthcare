<?php

namespace App\Http\Controllers\Operations\JobPortal;

use App\Enums\VacancyWorkflowStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobPortalDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $totalVacancies = Vacancy::query()->count();
        $activeVacancies = Vacancy::query()
            ->where('is_active', true)
            ->where('workflow_status', VacancyWorkflowStatus::Published)
            ->where(function ($q): void {
                $q->whereNull('closing_date')
                    ->orWhereDate('closing_date', '>=', now()->toDateString());
            })
            ->count();
        $newApplications = Application::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $whatsappApplies = Application::query()
            ->where(function ($q): void {
                $q->where('source', 'whatsapp')
                    ->orWhereNotNull('whatsapp_clicked_at');
            })
            ->count();
        $publishedJobs = Vacancy::query()
            ->where('workflow_status', VacancyWorkflowStatus::Published)
            ->count();

        return view('operations.job-portal.dashboard', [
            'metrics' => [
                'total_vacancies' => $totalVacancies,
                'active_vacancies' => $activeVacancies,
                'new_applications' => $newApplications,
                'whatsapp_applies' => $whatsappApplies,
                'published_jobs' => $publishedJobs,
            ],
        ]);
    }
}

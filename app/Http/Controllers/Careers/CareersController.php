<?php

namespace App\Http\Controllers\Careers;

use App\Enums\ApplicationPipelineStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Careers\StoreJobApplicationRequest;
use App\Models\Application;
use App\Models\Vacancy;
use App\Support\JobPostingSchema;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CareersController extends Controller
{
    public function index(): View
    {
        $vacancies = Vacancy::query()->careersListing()->paginate(12);

        return view('careers.index', compact('vacancies'));
    }

    public function show(string $slug): View
    {
        $vacancy = Vacancy::query()->careersListing()->where('slug', $slug)->firstOrFail();
        $schema = JobPostingSchema::forVacancy($vacancy);

        return view('careers.show', compact('vacancy', 'schema'));
    }

    public function storeApplication(StoreJobApplicationRequest $request, string $slug): RedirectResponse
    {
        $vacancy = Vacancy::query()->careersListing()->where('slug', $slug)->firstOrFail();

        $data = $request->validated();
        $whatsappClick = (bool) ($data['whatsapp_click'] ?? false);
        unset($data['whatsapp_click']);

        Application::query()->create([
            'vacancy_id' => $vacancy->id,
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'pin_code' => $data['pin_code'] ?? null,
            'city' => $data['city'] ?? null,
            'cover_message' => $data['cover_message'] ?? null,
            'source' => $whatsappClick ? 'whatsapp' : ($data['source'] ?? 'web'),
            'whatsapp_clicked_at' => $whatsappClick ? now() : null,
            'pipeline_status' => ApplicationPipelineStatus::Applied,
        ]);

        return redirect()
            ->route('careers.show', ['slug' => $slug])
            ->with('status', 'application-received');
    }
}

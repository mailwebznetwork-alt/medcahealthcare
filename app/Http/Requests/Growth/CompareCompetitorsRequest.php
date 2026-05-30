<?php

namespace App\Http\Requests\Growth;

use App\Models\Competitor;
use Illuminate\Foundation\Http\FormRequest;

class CompareCompetitorsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Competitor::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'competitor_ids' => ['required', 'array', 'min:2', 'max:10'],
            'competitor_ids.*' => ['required', 'integer', 'exists:competitors,id'],
        ];
    }
}

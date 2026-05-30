<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\UpdateLegacyModuleFieldsRequest;
use App\Models\Module;
use App\Services\DynamicModules\LegacyModuleSchemaService;
use Illuminate\Http\RedirectResponse;

class LegacyModuleFieldsController extends Controller
{
    public function __construct(
        private readonly LegacyModuleSchemaService $schemaService,
    ) {}

    public function update(UpdateLegacyModuleFieldsRequest $request, Module $module): RedirectResponse
    {
        $this->schemaService->sync($module, $request->validated());

        return back()->with('status', 'legacy-module-fields-updated');
    }
}

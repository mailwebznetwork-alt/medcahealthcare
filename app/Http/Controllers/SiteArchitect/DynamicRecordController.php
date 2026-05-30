<?php

namespace App\Http\Controllers\SiteArchitect;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Services\DynamicModules\DynamicRecordRepository;
use App\Services\DynamicModules\DynamicRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use stdClass;

class DynamicRecordController extends Controller
{
    public function __construct(
        private readonly DynamicRecordService $records,
        private readonly DynamicRecordRepository $repository,
    ) {}

    public function index(Module $module): View
    {
        $this->authorize('manageRecords', $module);

        $module->load('fieldDefinitions');
        $recordRows = $this->repository->paginate($module);
        $indexFields = $this->repository->listColumnsForIndex($module);

        return view('site-architect.modules.records.index', compact('module', 'recordRows', 'indexFields'));
    }

    public function create(Module $module): View
    {
        $this->authorize('manageRecords', $module);

        $module->load('fieldDefinitions');
        $record = new stdClass;

        return view('site-architect.modules.records.create', compact('module', 'record'));
    }

    public function store(Module $module): RedirectResponse
    {
        $this->authorize('manageRecords', $module);

        $values = $this->records->validateAndExtract(request(), $module);
        $this->records->create($module, $values);

        return redirect()
            ->route('site-architect.modules.records.index', $module)
            ->with('status', 'record-created');
    }

    public function edit(Module $module, int $record): View
    {
        $this->authorize('manageRecords', $module);

        $module->load('fieldDefinitions');
        $record = $this->repository->find($module, $record);

        abort_if($record === null, 404);

        return view('site-architect.modules.records.edit', compact('module', 'record'));
    }

    public function update(Module $module, int $record): RedirectResponse
    {
        $this->authorize('manageRecords', $module);

        abort_if($this->repository->find($module, $record) === null, 404);

        $values = $this->records->validateAndExtract(request(), $module);
        $this->records->update($module, $record, $values);

        return redirect()
            ->route('site-architect.modules.records.index', $module)
            ->with('status', 'record-updated');
    }

    public function destroy(Module $module, int $record): RedirectResponse
    {
        $this->authorize('manageRecords', $module);

        abort_if($this->repository->find($module, $record) === null, 404);

        $this->repository->delete($module, $record);

        return redirect()
            ->route('site-architect.modules.records.index', $module)
            ->with('status', 'record-deleted');
    }
}

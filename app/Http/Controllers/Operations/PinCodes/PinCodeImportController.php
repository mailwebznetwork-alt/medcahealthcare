<?php

namespace App\Http\Controllers\Operations\PinCodes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\PinCodes\ImportPinCodesRequest;
use App\Models\PinCode;
use App\Services\PinCodes\PinCodeCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PinCodeImportController extends Controller
{
    public function create(): View
    {
        $this->authorize('import', PinCode::class);

        return view('operations.pin-codes.import');
    }

    public function store(ImportPinCodesRequest $request, PinCodeCsvImporter $importer): RedirectResponse
    {
        $result = $importer->import($request->file('file'));

        return redirect()
            ->route('operations.pin-codes.index')
            ->with('import_result', $result);
    }
}

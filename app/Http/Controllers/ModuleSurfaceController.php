<?php

namespace App\Http\Controllers;

use App\ModuleAccess;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleSurfaceController extends Controller
{
    /**
     * Minimal workspace shell for a module (expand with real features later).
     */
    public function show(Request $request): View
    {
        /** @var string $key */
        $key = $request->route('momModule');

        if (! is_string($key) || ! ModuleAccess::isValidKey($key)) {
            abort(404);
        }

        $meta = ModuleAccess::navigation()[$key];

        return view('modules.surface', [
            'title' => $meta['label'],
        ]);
    }
}

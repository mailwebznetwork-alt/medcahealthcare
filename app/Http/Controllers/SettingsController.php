<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __invoke(): View
    {
        return view('modules.surface', [
            'title' => __('Settings'),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class ThemePreviewController extends Controller
{
    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user === null || ! in_array(strtolower((string) $user->role), ['admin', 'super_admin'], true)) {
            abort(403);
        }

        Session::put('theme_preview_public', true);

        return redirect()->to($request->input('redirect', url('/')))->with('status', __('Theme preview enabled.'));
    }

    public function disable(Request $request): RedirectResponse
    {
        Session::forget('theme_preview_public');

        return redirect()->to($request->input('redirect', route('settings.appearance')))->with('status', __('Theme preview disabled.'));
    }
}

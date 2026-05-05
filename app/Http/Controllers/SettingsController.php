<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __invoke(): View
    {
        /** @var Collection<int, Integration> $integrations */
        $integrations = collect();

        if (Schema::hasTable('integrations')) {
            $integrations = Integration::query()
                ->orderBy('type')
                ->orderBy('name')
                ->get();
        }

        return view('settings.integrations', [
            'integrations' => $integrations,
        ]);
    }
}

<?php

namespace App\Services\DynamicModules;

use App\Models\Module;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class DynamicModuleRenderer
{
    public function __construct(
        private readonly DynamicModuleInsertCatalog $catalog,
    ) {}

    public function render(string $slug): string
    {
        if (! Schema::hasTable('modules')) {
            return '';
        }

        $module = Module::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with('fieldDefinitions')
            ->first();

        if ($module === null) {
            return '';
        }

        $records = $this->catalog->recordsForPublic($module);

        if ($records->isEmpty()) {
            return '';
        }

        return View::make('dynamic-modules.public-listing', [
            'module' => $module,
            'records' => $records,
        ])->render();
    }
}

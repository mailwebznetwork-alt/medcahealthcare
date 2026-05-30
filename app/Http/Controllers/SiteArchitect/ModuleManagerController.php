<?php

namespace App\Http\Controllers\SiteArchitect;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiteArchitect\StoreModuleRequest;
use App\Http\Requests\SiteArchitect\UpdateModuleRequest;
use App\Models\FieldDefinition;
use App\Models\Module;
use App\Services\DynamicModules\DynamicTableManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ModuleManagerController extends Controller
{
    public function __construct(
        private readonly DynamicTableManager $tableManager,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Module::class);

        $modules = Module::query()
            ->withCount('fieldDefinitions')
            ->orderBy('name')
            ->get();

        return view('site-architect.modules.index', compact('modules'));
    }

    public function create(): View
    {
        $this->authorize('create', Module::class);

        $fieldTypes = FieldDefinition::typeLabels();

        return view('site-architect.modules.create', compact('fieldTypes'));
    }

    public function store(StoreModuleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $slug = filled($data['slug'] ?? null)
            ? Str::slug($data['slug'])
            : Str::slug($data['name']);

        $module = DB::transaction(function () use ($data, $slug): Module {
            $module = Module::query()->create([
                'name' => $data['name'],
                'slug' => $slug,
                'table_name' => Module::tableNameForSlug($slug),
                'settings' => [],
                'is_active' => true,
            ]);

            $fields = $this->persistFieldDefinitions($module, $data['fields']);
            $this->tableManager->createTable($module, $fields);

            return $module;
        });

        return redirect()
            ->route('site-architect.modules.records.index', $module)
            ->with('status', 'module-created');
    }

    public function edit(Module $module): View
    {
        $this->authorize('update', $module);

        $module->load('fieldDefinitions');
        $fieldTypes = FieldDefinition::typeLabels();

        return view('site-architect.modules.edit', compact('module', 'fieldTypes'));
    }

    public function update(UpdateModuleRequest $request, Module $module): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($module, $data): void {
            $module->update([
                'name' => $data['name'],
                'is_active' => (bool) ($data['is_active'] ?? $module->is_active),
            ]);

            $existingIds = collect($data['fields'])
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all();

            $removedColumnNames = $module->fieldDefinitions()
                ->whereNotIn('id', $existingIds)
                ->pluck('field_name')
                ->all();

            $module->fieldDefinitions()
                ->whereNotIn('id', $existingIds)
                ->delete();

            $this->persistFieldDefinitions($module, $data['fields'], true);
            $module->load('fieldDefinitions');
            $this->tableManager->syncFields($module, $removedColumnNames);
        });

        return redirect()
            ->route('site-architect.modules.edit', $module)
            ->with('status', 'module-updated');
    }

    public function destroy(Module $module): RedirectResponse
    {
        $this->authorize('delete', $module);

        DB::transaction(function () use ($module): void {
            $this->tableManager->dropTable($module);
            $module->delete();
        });

        return redirect()
            ->route('site-architect.modules.index')
            ->with('status', 'module-deleted');
    }

    /**
     * @param  list<array<string, mixed>>  $fieldsInput
     * @return list<FieldDefinition>
     */
    private function persistFieldDefinitions(Module $module, array $fieldsInput, bool $allowUpdates = false): array
    {
        $saved = [];

        foreach (array_values($fieldsInput) as $index => $fieldInput) {
            $attributes = [
                'label' => $fieldInput['label'],
                'field_name' => $fieldInput['field_name'],
                'field_type' => $fieldInput['field_type'],
                'is_required' => (bool) ($fieldInput['is_required'] ?? false),
                'sort_order' => $index,
                'settings' => $this->settingsFromInput($fieldInput),
            ];

            if ($allowUpdates && filled($fieldInput['id'] ?? null)) {
                $field = FieldDefinition::query()
                    ->where('module_id', $module->id)
                    ->whereKey($fieldInput['id'])
                    ->firstOrFail();
                $field->update($attributes);
            } else {
                $field = $module->fieldDefinitions()->create($attributes);
            }

            $saved[] = $field;
        }

        return $saved;
    }

    /**
     * @param  array<string, mixed>  $fieldInput
     * @return array<string, mixed>
     */
    private function settingsFromInput(array $fieldInput): array
    {
        if (($fieldInput['field_type'] ?? '') !== FieldDefinition::TYPE_SELECT) {
            return [];
        }

        $raw = (string) ($fieldInput['options'] ?? '');
        $options = array_values(array_filter(array_map(
            static fn (string $part): string => trim($part),
            preg_split('/\r\n|\r|\n|,/', $raw) ?: []
        ), static fn (string $option): bool => $option !== ''));

        return ['options' => $options];
    }
}

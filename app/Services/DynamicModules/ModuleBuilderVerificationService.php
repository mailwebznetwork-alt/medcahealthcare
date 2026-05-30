<?php

namespace App\Services\DynamicModules;

use App\Models\FieldDefinition;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ModuleBuilderVerificationService
{
    public const VERIFY_MODULE_NAME = 'TestProduct';

    public const VERIFY_MODULE_SLUG = 'test-products';

    public function __construct(
        private readonly DynamicTableManager $tableManager,
        private readonly DynamicRecordRepository $records,
        private readonly DynamicFieldValidator $validator,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function run(bool $cleanup = true): array
    {
        $report = [
            'passed' => true,
            'checks' => [],
            'module' => null,
            'table_name' => Module::tableNameForSlug(self::VERIFY_MODULE_SLUG),
            'note' => 'Dynamic tables use the mod_{slug} convention (e.g. mod_test_products), not a bare test_products table.',
        ];

        try {
            $this->cleanupExistingVerificationModule();

            $module = $this->createVerificationModule();
            $report['module'] = $module->only(['id', 'name', 'slug', 'table_name']);

            $report['checks'][] = $this->checkSchemaIntegrity($module);
            $report['checks'][] = $this->checkCrudFlow($module);
            $report['checks'][] = $this->checkValidationRules($module);
            $report['checks'][] = $this->checkFieldRemovalColumnCleanup($module);

            $report['passed'] = collect($report['checks'])->every(
                static fn (array $check): bool => ($check['passed'] ?? false) === true
            );
        } catch (Throwable $exception) {
            $report['passed'] = false;
            $report['checks'][] = [
                'name' => 'Unhandled verification exception',
                'passed' => false,
                'details' => $exception->getMessage(),
            ];
        } finally {
            if ($cleanup) {
                $this->cleanupExistingVerificationModule();
            }
        }

        return $report;
    }

    private function createVerificationModule(): Module
    {
        return DB::transaction(function (): Module {
            $module = Module::query()->create([
                'name' => self::VERIFY_MODULE_NAME,
                'slug' => self::VERIFY_MODULE_SLUG,
                'table_name' => Module::tableNameForSlug(self::VERIFY_MODULE_SLUG),
                'settings' => ['verification' => true],
                'is_active' => true,
            ]);

            $fields = [
                $module->fieldDefinitions()->create([
                    'field_name' => 'sku',
                    'label' => 'SKU',
                    'field_type' => FieldDefinition::TYPE_TEXT,
                    'is_required' => true,
                    'sort_order' => 0,
                ]),
                $module->fieldDefinitions()->create([
                    'field_name' => 'price',
                    'label' => 'Price',
                    'field_type' => FieldDefinition::TYPE_NUMBER,
                    'is_required' => true,
                    'sort_order' => 1,
                ]),
            ];

            $this->tableManager->createTable($module, $fields);

            return $module->fresh(['fieldDefinitions']);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function checkSchemaIntegrity(Module $module): array
    {
        $tableName = $module->table_name;
        $columns = Schema::hasTable($tableName)
            ? Schema::getColumnListing($tableName)
            : [];

        $tableExists = Schema::hasTable($tableName);
        $skuExists = in_array('sku', $columns, true);
        $priceExists = in_array('price', $columns, true);
        $fieldDefinitionCount = $module->fieldDefinitions()->count();

        return [
            'name' => 'Schema integrity',
            'passed' => $tableExists && $skuExists && $priceExists && $fieldDefinitionCount === 2,
            'details' => [
                'expected_table' => $tableName,
                'legacy_alias_test_products' => 'test_products (not used — see mod_{slug} convention)',
                'table_exists' => $tableExists,
                'columns_found' => $columns,
                'sku_column_exists' => $skuExists,
                'price_column_exists' => $priceExists,
                'field_definitions' => $fieldDefinitionCount,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function checkCrudFlow(Module $module): array
    {
        $recordId = $this->records->create($module, [
            'sku' => 'MEDCA-SKU-001',
            'price' => '1299.50',
        ]);

        $record = $this->records->find($module, $recordId);

        $createOk = $record !== null
            && ($record->sku ?? null) === 'MEDCA-SKU-001'
            && is_numeric($record->price ?? null)
            && (float) $record->price === 1299.5;

        $this->records->update($module, $recordId, [
            'sku' => 'MEDCA-SKU-002',
            'price' => '1499.00',
        ]);

        $updated = $this->records->find($module, $recordId);
        $updateOk = $updated !== null && ($updated->sku ?? null) === 'MEDCA-SKU-002';

        $this->records->delete($module, $recordId);
        $deleteOk = $this->records->find($module, $recordId) === null;

        return [
            'name' => 'CRUD flow',
            'passed' => $createOk && $updateOk && $deleteOk,
            'details' => [
                'create' => $createOk,
                'update' => $updateOk,
                'delete' => $deleteOk,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function checkValidationRules(Module $module): array
    {
        $module->load('fieldDefinitions');

        $rules = $this->validator->rulesFor($module, 'fields');
        $priceRules = $rules['fields.price'] ?? [];
        $hasNumericRule = in_array('numeric', $priceRules, true);

        $invalid = Validator::make(
            ['fields' => ['sku' => 'ABC', 'price' => 'not-a-number']],
            $rules
        );

        $valid = Validator::make(
            ['fields' => ['sku' => 'ABC', 'price' => '10.5']],
            $rules
        );

        return [
            'name' => 'Dynamic validation',
            'passed' => $hasNumericRule && $invalid->fails() && $valid->passes(),
            'details' => [
                'field_definition_count' => $module->fieldDefinitions->count(),
                'price_rules' => $priceRules,
                'invalid_payload_rejected' => $invalid->fails(),
                'invalid_price_errors' => $invalid->errors()->get('fields.price'),
                'valid_payload_accepted' => $valid->passes(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function checkFieldRemovalColumnCleanup(Module $module): array
    {
        $skuField = $module->fieldDefinitions()->where('field_name', 'sku')->first();
        $skuColumnBefore = Schema::hasColumn($module->table_name, 'sku');

        if ($skuField === null) {
            return [
                'name' => 'Field removal column cleanup',
                'passed' => false,
                'details' => ['error' => 'SKU field definition missing before removal test.'],
            ];
        }

        $skuField->delete();
        $module->load('fieldDefinitions');
        $this->tableManager->syncFields($module, ['sku']);

        $skuColumnAfter = Schema::hasColumn($module->table_name, 'sku');
        $priceColumnAfter = Schema::hasColumn($module->table_name, 'price');
        $remainingFields = $module->fieldDefinitions()->pluck('field_name')->all();

        return [
            'name' => 'Field removal column cleanup',
            'passed' => $skuColumnBefore && ! $skuColumnAfter && $priceColumnAfter && $remainingFields === ['price'],
            'details' => [
                'sku_column_before' => $skuColumnBefore,
                'sku_column_after' => $skuColumnAfter,
                'price_column_preserved' => $priceColumnAfter,
                'remaining_field_definitions' => $remainingFields,
            ],
        ];
    }

    private function cleanupExistingVerificationModule(): void
    {
        $tableName = Module::tableNameForSlug(self::VERIFY_MODULE_SLUG);

        $module = Module::query()
            ->where('slug', self::VERIFY_MODULE_SLUG)
            ->orWhere('table_name', $tableName)
            ->first();

        if ($module === null) {
            if (Schema::hasTable($tableName)) {
                Schema::drop($tableName);
            }

            return;
        }

        DB::transaction(function () use ($module): void {
            $this->tableManager->dropTable($module);
            $module->fieldDefinitions()->delete();
            $module->delete();
        });
    }
}

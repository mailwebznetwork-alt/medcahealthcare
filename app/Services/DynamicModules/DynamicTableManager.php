<?php

namespace App\Services\DynamicModules;

use App\Models\FieldDefinition;
use App\Models\Module;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use RuntimeException;

class DynamicTableManager
{
    /**
     * @param  Collection<int, FieldDefinition>|list<FieldDefinition>  $fields
     */
    public function createTable(Module $module, Collection|array $fields = []): void
    {
        $this->assertValidTableName($module->table_name, $module->isLegacy());

        if (Schema::hasTable($module->table_name)) {
            throw new RuntimeException("Table [{$module->table_name}] already exists.");
        }

        $fieldList = $fields instanceof Collection ? $fields->all() : $fields;

        Schema::create($module->table_name, function (Blueprint $table) use ($fieldList): void {
            $table->id();

            foreach ($fieldList as $field) {
                $this->addColumn($table, $field);
            }

            $table->timestamps();
        });
    }

    public function syncFields(Module $module, array $removedColumnNames = []): void
    {
        $this->assertValidTableName($module->table_name, $module->isLegacy());

        if (! Schema::hasTable($module->table_name)) {
            $this->createTable($module, $module->fieldDefinitions);

            return;
        }

        $existing = collect(Schema::getColumnListing($module->table_name));

        foreach ($module->fieldDefinitions as $field) {
            if ($existing->contains($field->field_name)) {
                continue;
            }

            Schema::table($module->table_name, function (Blueprint $table) use ($field): void {
                $this->addColumn($table, $field);
            });

            $existing->push($field->field_name);
        }

        foreach ($removedColumnNames as $columnName) {
            if (! is_string($columnName) || $columnName === '') {
                continue;
            }

            if (! $existing->contains($columnName)) {
                continue;
            }

            $this->assertValidColumnName($columnName);

            Schema::table($module->table_name, function (Blueprint $table) use ($columnName): void {
                $table->dropColumn($columnName);
            });
        }
    }

    public function dropTable(Module $module): void
    {
        if (Schema::hasTable($module->table_name)) {
            Schema::drop($module->table_name);
        }
    }

    private function addColumn(Blueprint $table, FieldDefinition $field): void
    {
        $name = $field->field_name;
        $this->assertValidColumnName($name);

        match ($field->field_type) {
            FieldDefinition::TYPE_TEXT,
            FieldDefinition::TYPE_EMAIL,
            FieldDefinition::TYPE_URL,
            FieldDefinition::TYPE_SELECT => $table->string($name)->nullable(),
            FieldDefinition::TYPE_TEXTAREA => $table->text($name)->nullable(),
            FieldDefinition::TYPE_NUMBER => $table->decimal($name, 15, 4)->nullable(),
            FieldDefinition::TYPE_BOOLEAN => $table->boolean($name)->default(false),
            FieldDefinition::TYPE_DATE => $table->date($name)->nullable(),
            default => throw new InvalidArgumentException("Unsupported field type [{$field->field_type}]."),
        };
    }

    private function assertValidTableName(string $tableName, bool $legacy = false): void
    {
        if ($tableName === '') {
            throw new InvalidArgumentException('Invalid dynamic module table name [empty].');
        }

        if ($legacy) {
            if (! preg_match('/^[a-z][a-z0-9_]*$/', $tableName)) {
                throw new InvalidArgumentException("Invalid legacy module table name [{$tableName}].");
            }

            return;
        }

        if (! preg_match('/^mod_[a-z0-9_]+$/', $tableName)) {
            throw new InvalidArgumentException("Invalid dynamic module table name [{$tableName}].");
        }
    }

    private function assertValidColumnName(string $columnName): void
    {
        $reserved = ['id', 'created_at', 'updated_at'];

        if (in_array($columnName, $reserved, true)) {
            throw new InvalidArgumentException("Column name [{$columnName}] is reserved.");
        }

        if ($columnName === '' || ! preg_match('/^[a-z][a-z0-9_]*$/', $columnName)) {
            throw new InvalidArgumentException("Invalid column name [{$columnName}].");
        }
    }
}

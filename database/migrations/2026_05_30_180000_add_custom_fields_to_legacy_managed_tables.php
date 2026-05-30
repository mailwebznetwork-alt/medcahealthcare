<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['services', 'pin_codes', 'vacancies'] as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'custom_fields')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->json('custom_fields')->nullable()->after('updated_at');
            });
        }
    }

    public function down(): void
    {
        foreach (['services', 'pin_codes', 'vacancies'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'custom_fields')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropColumn('custom_fields');
            });
        }
    }
};

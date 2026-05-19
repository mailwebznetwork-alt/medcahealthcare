<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('layout_mode', 32)->default('contained')->after('is_active');
        });

        if (Schema::hasTable('pages')) {
            DB::table('pages')
                ->whereIn('slug', ['careers', 'services', 'locations', 'home'])
                ->update(['layout_mode' => 'canvas']);
        }
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('layout_mode');
        });
    }
};

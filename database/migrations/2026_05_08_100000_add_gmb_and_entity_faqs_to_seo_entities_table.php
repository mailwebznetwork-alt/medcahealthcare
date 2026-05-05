<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_entities', function (Blueprint $table): void {
            if (! Schema::hasColumn('seo_entities', 'google_place_id')) {
                $table->string('google_place_id', 256)->nullable()->after('custom_json_ld');
            }
            if (! Schema::hasColumn('seo_entities', 'google_business_profile_url')) {
                $table->string('google_business_profile_url', 2048)->nullable()->after('google_place_id');
            }
            if (! Schema::hasColumn('seo_entities', 'has_map_url')) {
                $table->string('has_map_url', 2048)->nullable()->after('google_business_profile_url');
            }
            if (! Schema::hasColumn('seo_entities', 'entity_faqs')) {
                $table->json('entity_faqs')->nullable()->after('has_map_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seo_entities', function (Blueprint $table): void {
            foreach (['entity_faqs', 'has_map_url', 'google_business_profile_url', 'google_place_id'] as $column) {
                if (Schema::hasColumn('seo_entities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

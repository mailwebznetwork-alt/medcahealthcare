<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('competitor_keywords') && ! Schema::hasColumn('competitor_keywords', 'hijack_priority')) {
            Schema::table('competitor_keywords', function (Blueprint $table): void {
                $table->unsignedTinyInteger('hijack_priority')->nullable()->after('difficulty');
                $table->index('hijack_priority');
            });
        }

        if (Schema::hasTable('seo_entities') && ! Schema::hasColumn('seo_entities', 'hijack_strategy')) {
            Schema::table('seo_entities', function (Blueprint $table): void {
                $table->text('hijack_strategy')->nullable()->after('entity_faqs');
            });
        }

        if (! Schema::hasTable('site_keyword_rankings')) {
            Schema::create('site_keyword_rankings', function (Blueprint $table): void {
                $table->id();
                $table->string('keyword');
                $table->unsignedSmallInteger('position')->nullable();
                $table->date('recorded_date');
                $table->timestamps();

                $table->index('keyword');
                $table->index(['keyword', 'recorded_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_keyword_rankings');

        if (Schema::hasTable('seo_entities') && Schema::hasColumn('seo_entities', 'hijack_strategy')) {
            Schema::table('seo_entities', function (Blueprint $table): void {
                $table->dropColumn('hijack_strategy');
            });
        }

        if (Schema::hasTable('competitor_keywords') && Schema::hasColumn('competitor_keywords', 'hijack_priority')) {
            Schema::table('competitor_keywords', function (Blueprint $table): void {
                $table->dropIndex(['hijack_priority']);
                $table->dropColumn('hijack_priority');
            });
        }
    }
};

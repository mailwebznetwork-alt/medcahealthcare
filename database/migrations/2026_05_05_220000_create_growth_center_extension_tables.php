<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('seo_entities')) {
            Schema::create('seo_entities', function (Blueprint $table): void {
                $table->id();
                $table->string('organization_name');
                $table->string('website')->nullable();
                $table->string('default_language', 12)->default('en');
                $table->string('knowledge_graph_id')->nullable();
                $table->json('social_profiles')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seo_technical')) {
            Schema::create('seo_technical', function (Blueprint $table): void {
                $table->id();
                $table->boolean('robots_enabled')->default(true);
                $table->boolean('sitemap_enabled')->default(true);
                $table->string('canonical_mode', 40)->default('self');
                $table->text('robots_content')->nullable();
                $table->string('sitemap_url')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seo_ai_signals')) {
            Schema::create('seo_ai_signals', function (Blueprint $table): void {
                $table->id();
                $table->boolean('ai_crawl_enabled')->default(false);
                $table->unsignedTinyInteger('llm_visibility_score')->default(0);
                $table->unsignedTinyInteger('entity_consistency_score')->default(0);
                $table->json('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('geo_locations')) {
            Schema::create('geo_locations', function (Blueprint $table): void {
                $table->id();
                $table->string('label');
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->unsignedInteger('radius_km')->default(25);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pincodes')) {
            Schema::create('pincodes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('geo_location_id')->nullable()->constrained('geo_locations')->nullOnDelete();
                $table->string('code', 20)->unique();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('intercepts')) {
            Schema::create('intercepts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('competitor_id')->nullable()->constrained('competitors')->nullOnDelete();
                $table->string('title');
                $table->string('channel', 40);
                $table->unsignedTinyInteger('priority')->default(2);
                $table->string('status', 40)->default('active');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['status', 'priority']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('intercepts');
        Schema::dropIfExists('pincodes');
        Schema::dropIfExists('geo_locations');
        Schema::dropIfExists('seo_ai_signals');
        Schema::dropIfExists('seo_technical');
        Schema::dropIfExists('seo_entities');
    }
};

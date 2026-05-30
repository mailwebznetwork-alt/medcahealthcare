<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('competitor_backlinks')) {
            Schema::create('competitor_backlinks', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('competitor_id')->constrained('competitors')->cascadeOnDelete();
                $table->string('referring_domain', 255);
                $table->string('target_url', 2048)->nullable();
                $table->string('anchor_text', 512)->nullable();
                $table->string('discovery_method', 40)->default('api');
                $table->string('status', 20)->default('active');
                $table->timestamp('last_checked_at')->nullable();
                $table->timestamps();

                $table->unique(['competitor_id', 'referring_domain'], 'competitor_backlinks_competitor_domain_unique');
                $table->index('referring_domain');
            });
        }

        if (! Schema::hasTable('site_backlinks')) {
            Schema::create('site_backlinks', function (Blueprint $table): void {
                $table->id();
                $table->string('referring_domain', 255)->unique();
                $table->string('target_url', 2048)->nullable();
                $table->string('anchor_text', 512)->nullable();
                $table->string('source', 40)->default('manual');
                $table->string('status', 20)->default('active');
                $table->timestamp('last_checked_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_backlinks');
        Schema::dropIfExists('competitor_backlinks');
    }
};

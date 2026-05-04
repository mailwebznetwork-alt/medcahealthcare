<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('service_code')->unique();
            $table->text('short_summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('price_range')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('icon')->nullable();
            $table->json('gallery')->nullable();
            $table->string('image_alt')->nullable();
            $table->json('target_keywords')->nullable();
            $table->json('ai_keywords')->nullable();
            $table->unsignedInteger('quality_score')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->enum('publish_status', ['draft', 'published'])->default('draft');
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['publish_status', 'is_active']);
            $table->index('is_featured');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

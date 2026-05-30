<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_presets', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('shell', 16)->default('public');
            $table->boolean('is_builtin')->default(false);
            $table->json('tokens');
            $table->json('branding')->nullable();
            $table->string('header_preset', 32)->nullable();
            $table->string('layout_preset', 32)->nullable();
            $table->json('typography')->nullable();
            $table->timestamps();
        });

        Schema::create('theme_configurations', function (Blueprint $table) {
            $table->id();
            $table->json('published_public')->nullable();
            $table->json('draft_public')->nullable();
            $table->json('published_admin')->nullable();
            $table->json('draft_admin')->nullable();
            $table->json('branding')->nullable();
            $table->json('draft_branding')->nullable();
            $table->json('typography')->nullable();
            $table->json('draft_typography')->nullable();
            $table->string('header_preset', 32)->default('classic_healthcare');
            $table->string('layout_preset', 32)->default('contained');
            $table->string('draft_header_preset', 32)->nullable();
            $table->string('draft_layout_preset', 32)->nullable();
            $table->string('active_preset_slug')->nullable();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('draft_updated_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_configurations');
        Schema::dropIfExists('theme_presets');
    }
};

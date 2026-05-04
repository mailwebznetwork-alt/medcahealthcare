<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_seo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('focus_keywords')->nullable();
            $table->string('h1')->nullable();
            $table->json('h2')->nullable();
            $table->json('h3')->nullable();
            $table->text('ai_context')->nullable();
            $table->string('search_intent')->nullable();
            $table->timestamps();

            $table->unique('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_seo');
    }
};

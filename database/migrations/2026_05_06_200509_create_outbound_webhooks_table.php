<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('outbound_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('target_url', 2048);
            $table->string('http_method', 12)->default('POST');
            $table->text('secret')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->longText('payload_template')->nullable();
            $table->json('custom_headers')->nullable();
            $table->text('auth_bearer_token')->nullable();
            $table->boolean('enforce_https')->default(true);
            $table->unsignedTinyInteger('max_retries')->default(3);
            $table->unsignedSmallInteger('timeout_seconds')->default(15);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('events');
            $table->timestamps();

            $table->index(['is_enabled', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbound_webhooks');
    }
};

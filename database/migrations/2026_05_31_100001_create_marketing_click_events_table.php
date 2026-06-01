<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_click_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 64)->index();
            $table->string('page_path', 500)->nullable()->index();
            $table->string('page_title', 255)->nullable();
            $table->string('campaign', 255)->nullable()->index();
            $table->string('source', 128)->nullable()->index();
            $table->string('medium', 128)->nullable();
            $table->string('element_label', 255)->nullable();
            $table->string('destination_url', 500)->nullable();
            $table->string('device_type', 32)->nullable()->index();
            $table->string('browser', 64)->nullable();
            $table->string('operating_system', 64)->nullable();
            $table->string('session_fingerprint', 64)->nullable()->index();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['event_type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_click_events');
    }
};

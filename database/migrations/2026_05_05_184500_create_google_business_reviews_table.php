<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_business_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('integration_id')->nullable()->constrained('integrations')->nullOnDelete();
            $table->string('review_id')->unique();
            $table->string('reviewer_name')->nullable();
            $table->unsignedTinyInteger('star_rating')->default(0);
            $table->text('comment')->nullable();
            $table->timestamp('review_time')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_business_reviews');
    }
};

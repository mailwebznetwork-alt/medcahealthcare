<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('competitor_trackings')) {
            return;
        }

        Schema::create('competitor_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competitor_keyword_id')->constrained('competitor_keywords')->cascadeOnDelete();
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('position')->nullable();
            $table->date('recorded_date');
            $table->timestamps();

            $table->index('competitor_keyword_id');
            $table->index('recorded_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitor_trackings');
    }
};

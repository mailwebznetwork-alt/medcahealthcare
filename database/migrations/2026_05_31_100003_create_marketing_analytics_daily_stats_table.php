<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_analytics_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('stat_date')->index();
            $table->string('metric_group', 64)->index();
            $table->string('metric_key', 128)->index();
            $table->unsignedBigInteger('metric_value')->default(0);
            $table->json('dimensions')->nullable();
            $table->timestamps();

            $table->unique(['stat_date', 'metric_group', 'metric_key'], 'marketing_daily_stats_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_analytics_daily_stats');
    }
};

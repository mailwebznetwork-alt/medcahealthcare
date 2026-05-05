<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('competitor_leads')) {
            return;
        }

        Schema::create('competitor_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competitor_keyword_id')->nullable()->constrained('competitor_keywords')->nullOnDelete();
            $table->string('source');
            $table->json('details')->nullable();
            $table->string('status');
            $table->timestamps();

            $table->index('competitor_keyword_id');
            $table->index('source');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitor_leads');
    }
};

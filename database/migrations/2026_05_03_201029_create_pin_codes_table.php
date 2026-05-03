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
        Schema::create('pin_codes', function (Blueprint $table) {
            $table->id();
            $table->string('pincode', 12)->unique();
            $table->string('area_name');
            $table->string('city');
            $table->string('locality')->nullable();
            $table->boolean('is_serviceable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->decimal('delivery_charge', 10, 2)->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->string('slug', 255)->nullable()->unique();
            $table->boolean('geo_page_ready')->default(false);
            $table->timestamps();

            $table->index('city');
            $table->index(['is_active', 'is_serviceable']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pin_codes');
    }
};

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
        Schema::create('page_pin_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pin_code_id')->constrained('pin_codes')->cascadeOnDelete();
            $table->boolean('serviceability')->default(true);
            $table->decimal('delivery_charge', 12, 2)->nullable();
            $table->text('location_keywords')->nullable();
            $table->timestamps();

            $table->unique(['page_id', 'pin_code_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_pin_codes');
    }
};

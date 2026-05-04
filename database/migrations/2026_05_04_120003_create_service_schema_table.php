<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_schema', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('schema_type')->nullable();
            $table->json('schema_json')->nullable();
            $table->timestamps();

            $table->unique('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_schema');
    }
};

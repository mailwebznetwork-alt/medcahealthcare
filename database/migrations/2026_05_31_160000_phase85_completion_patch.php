<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('global_content_variable_snapshots')) {
            Schema::create('global_content_variable_snapshots', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('version')->default(1);
                $table->json('payload_json');
                $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('global_content_variable_snapshots');
    }
};

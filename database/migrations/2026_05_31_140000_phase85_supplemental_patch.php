<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_content_variables', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('label', 120);
            $table->text('value')->nullable();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('section_library_items', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('blocks_json');
            $table->string('style_pack_slug', 64)->nullable();
            $table->boolean('is_builtin')->default(false);
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('deployment_packages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('version', 32)->default('1.0.0');
            $table->unsignedInteger('package_version')->default(1);
            $table->json('manifest_json');
            $table->string('checksum', 64)->nullable();
            $table->foreignId('cloned_from_id')->nullable()->constrained('deployment_packages')->nullOnDelete();
            $table->foreignId('exported_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('imported_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('exported_at')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_packages');
        Schema::dropIfExists('section_library_items');
        Schema::dropIfExists('global_content_variables');
    }
};

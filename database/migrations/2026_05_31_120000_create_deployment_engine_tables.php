<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blocks', function (Blueprint $table) {
            if (! Schema::hasColumn('blocks', 'settings_json')) {
                $table->json('settings_json')->nullable()->after('schema_json');
            }
        });

        Schema::table('pages', function (Blueprint $table) {
            if (! Schema::hasColumn('pages', 'block_overrides_json')) {
                $table->json('block_overrides_json')->nullable()->after('layout_mode');
            }
            if (! Schema::hasColumn('pages', 'deployment_meta_json')) {
                $table->json('deployment_meta_json')->nullable()->after('block_overrides_json');
            }
        });

        Schema::table('theme_configurations', function (Blueprint $table) {
            if (! Schema::hasColumn('theme_configurations', 'published_shape')) {
                $table->json('published_shape')->nullable()->after('published_public');
            }
            if (! Schema::hasColumn('theme_configurations', 'draft_shape')) {
                $table->json('draft_shape')->nullable()->after('draft_public');
            }
            if (! Schema::hasColumn('theme_configurations', 'active_style_pack')) {
                $table->string('active_style_pack', 64)->nullable()->after('active_preset_slug');
            }
            if (! Schema::hasColumn('theme_configurations', 'draft_style_pack')) {
                $table->string('draft_style_pack', 64)->nullable()->after('active_style_pack');
            }
        });

        Schema::create('block_presets', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('block_type', 64)->nullable();
            $table->string('target_block_slug', 120)->nullable();
            $table->json('settings_json');
            $table->boolean('is_builtin')->default(false);
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('deployment_generations', function (Blueprint $table) {
            $table->id();
            $table->string('blueprint_slug', 64);
            $table->string('style_pack_slug', 64)->nullable();
            $table->string('theme_preset_slug', 64)->nullable();
            $table->string('layout_preset', 32)->nullable();
            $table->json('generated_page_slugs')->nullable();
            $table->string('status', 32)->default('draft');
            $table->foreignId('generated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_generations');
        Schema::dropIfExists('block_presets');

        Schema::table('theme_configurations', function (Blueprint $table) {
            foreach (['draft_style_pack', 'active_style_pack', 'draft_shape', 'published_shape'] as $col) {
                if (Schema::hasColumn('theme_configurations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('pages', function (Blueprint $table) {
            foreach (['deployment_meta_json', 'block_overrides_json'] as $col) {
                if (Schema::hasColumn('pages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('blocks', function (Blueprint $table) {
            if (Schema::hasColumn('blocks', 'settings_json')) {
                $table->dropColumn('settings_json');
            }
        });
    }
};

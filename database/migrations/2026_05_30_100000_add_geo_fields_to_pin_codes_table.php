<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pin_codes', function (Blueprint $table): void {
            if (! Schema::hasColumn('pin_codes', 'geo_location_id')) {
                $table->foreignId('geo_location_id')
                    ->nullable()
                    ->after('geo_page_ready')
                    ->constrained('geo_locations')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('pin_codes', 'business_profile_id')) {
                $table->foreignId('business_profile_id')
                    ->nullable()
                    ->after('geo_location_id')
                    ->constrained('business_profiles')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('pin_codes', 'landing_page')) {
                $table->string('landing_page')->nullable()->after('business_profile_id');
            }

            if (! Schema::hasColumn('pin_codes', 'priority')) {
                $table->string('priority', 20)->default('medium')->after('landing_page');
            }
        });

        if (! Schema::hasTable('pincodes')) {
            return;
        }

        $rows = DB::table('pincodes')->orderBy('id')->get();

        foreach ($rows as $row) {
            $pin = trim((string) ($row->pincode ?? $row->code ?? ''));
            if ($pin === '') {
                continue;
            }

            $existing = DB::table('pin_codes')->where('pincode', $pin)->first();

            if ($existing !== null) {
                DB::table('pin_codes')->where('id', $existing->id)->update([
                    'geo_location_id' => $row->geo_location_id ?? $existing->geo_location_id,
                    'business_profile_id' => $row->business_profile_id ?? $existing->business_profile_id,
                    'landing_page' => $row->landing_page ?? $existing->landing_page,
                    'priority' => $row->priority ?? $existing->priority ?? 'medium',
                    'is_serviceable' => property_exists($row, 'serviceable')
                        ? (bool) $row->serviceable
                        : (bool) $existing->is_serviceable,
                    'updated_at' => now(),
                ]);

                continue;
            }

            DB::table('pin_codes')->insert([
                'pincode' => $pin,
                'area_name' => filled($row->city ?? null) ? (string) $row->city : 'Area '.$pin,
                'city' => filled($row->city ?? null) ? (string) $row->city : 'Bangalore',
                'locality' => null,
                'is_serviceable' => (bool) ($row->serviceable ?? true),
                'is_active' => (bool) ($row->is_active ?? true),
                'delivery_charge' => null,
                'meta_title' => null,
                'meta_description' => null,
                'seo_keywords' => null,
                'slug' => null,
                'geo_page_ready' => false,
                'geo_location_id' => $row->geo_location_id,
                'business_profile_id' => $row->business_profile_id,
                'landing_page' => $row->landing_page,
                'priority' => $row->priority ?? 'medium',
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('pin_codes', function (Blueprint $table): void {
            if (Schema::hasColumn('pin_codes', 'business_profile_id')) {
                $table->dropConstrainedForeignId('business_profile_id');
            }

            if (Schema::hasColumn('pin_codes', 'geo_location_id')) {
                $table->dropConstrainedForeignId('geo_location_id');
            }

            if (Schema::hasColumn('pin_codes', 'landing_page')) {
                $table->dropColumn('landing_page');
            }

            if (Schema::hasColumn('pin_codes', 'priority')) {
                $table->dropColumn('priority');
            }
        });
    }
};

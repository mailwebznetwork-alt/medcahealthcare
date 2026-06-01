<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('lead_source', 128)->nullable()->after('campaign');
            $table->string('lead_medium', 128)->nullable()->after('lead_source');
            $table->string('lead_campaign', 255)->nullable()->after('lead_medium');
            $table->string('lead_content', 255)->nullable()->after('lead_campaign');
            $table->string('lead_term', 255)->nullable()->after('lead_content');
            $table->string('utm_source', 128)->nullable()->after('lead_term');
            $table->string('utm_medium', 128)->nullable()->after('utm_source');
            $table->string('utm_campaign', 255)->nullable()->after('utm_medium');
            $table->string('utm_content', 255)->nullable()->after('utm_campaign');
            $table->string('utm_term', 255)->nullable()->after('utm_content');
            $table->string('gclid', 255)->nullable()->after('utm_term');
            $table->string('fbclid', 255)->nullable()->after('gclid');
            $table->string('landing_page', 500)->nullable()->after('fbclid');
            $table->string('referrer_url', 500)->nullable()->after('landing_page');
            $table->string('first_touch_source', 128)->nullable()->after('referrer_url');
            $table->string('first_touch_medium', 128)->nullable()->after('first_touch_source');
            $table->string('first_touch_campaign', 255)->nullable()->after('first_touch_medium');
            $table->timestamp('first_touch_at')->nullable()->after('first_touch_campaign');
            $table->string('last_touch_source', 128)->nullable()->after('first_touch_at');
            $table->string('last_touch_medium', 128)->nullable()->after('last_touch_source');
            $table->string('last_touch_campaign', 255)->nullable()->after('last_touch_medium');
            $table->string('device_type', 32)->nullable()->after('last_touch_campaign');
            $table->string('browser', 64)->nullable()->after('device_type');
            $table->string('operating_system', 64)->nullable()->after('browser');
            $table->string('country', 64)->nullable()->after('operating_system');
            $table->string('region', 128)->nullable()->after('country');
            $table->string('city', 128)->nullable()->after('region');
            $table->string('pipeline_stage', 32)->nullable()->index()->after('status');
            $table->timestamp('pipeline_stage_changed_at')->nullable()->after('pipeline_stage');
            $table->timestamp('converted_at')->nullable()->after('pipeline_stage_changed_at');

            $table->index('utm_source');
            $table->index('utm_campaign');
            $table->index('gclid');
            $table->index('first_touch_source');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['utm_source']);
            $table->dropIndex(['utm_campaign']);
            $table->dropIndex(['gclid']);
            $table->dropIndex(['first_touch_source']);
            $table->dropColumn([
                'lead_source', 'lead_medium', 'lead_campaign', 'lead_content', 'lead_term',
                'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term',
                'gclid', 'fbclid', 'landing_page', 'referrer_url',
                'first_touch_source', 'first_touch_medium', 'first_touch_campaign', 'first_touch_at',
                'last_touch_source', 'last_touch_medium', 'last_touch_campaign',
                'device_type', 'browser', 'operating_system', 'country', 'region', 'city',
                'pipeline_stage', 'pipeline_stage_changed_at', 'converted_at',
            ]);
        });
    }
};

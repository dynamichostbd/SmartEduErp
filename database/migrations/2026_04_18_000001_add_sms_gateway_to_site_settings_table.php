<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        if (Schema::hasColumn('site_settings', 'sms_gateway')) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table) {
            if (Schema::hasColumn('site_settings', 'sms_cost')) {
                $table->string('sms_gateway')->nullable()->default('sslwireless')->after('sms_cost');
                return;
            }

            $table->string('sms_gateway')->nullable()->default('sslwireless');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        if (!Schema::hasColumn('site_settings', 'sms_gateway')) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('sms_gateway');
        });
    }
};

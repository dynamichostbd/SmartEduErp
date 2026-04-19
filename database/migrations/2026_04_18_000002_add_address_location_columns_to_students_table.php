<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('students')) {
            return;
        }

        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'division_id')) {
                $table->unsignedBigInteger('division_id')->nullable();
            }
            if (!Schema::hasColumn('students', 'district_id')) {
                $table->unsignedBigInteger('district_id')->nullable();
            }
            if (!Schema::hasColumn('students', 'upazila_id')) {
                $table->unsignedBigInteger('upazila_id')->nullable();
            }
            if (!Schema::hasColumn('students', 'union_id')) {
                $table->unsignedBigInteger('union_id')->nullable();
            }

            if (!Schema::hasColumn('students', 'permanent_division_id')) {
                $table->unsignedBigInteger('permanent_division_id')->nullable();
            }
            if (!Schema::hasColumn('students', 'permanent_district_id')) {
                $table->unsignedBigInteger('permanent_district_id')->nullable();
            }
            if (!Schema::hasColumn('students', 'permanent_upazila_id')) {
                $table->unsignedBigInteger('permanent_upazila_id')->nullable();
            }
            if (!Schema::hasColumn('students', 'permanent_union_id')) {
                $table->unsignedBigInteger('permanent_union_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('students')) {
            return;
        }

        Schema::table('students', function (Blueprint $table) {
            $cols = [
                'division_id',
                'district_id',
                'upazila_id',
                'union_id',
                'permanent_division_id',
                'permanent_district_id',
                'permanent_upazila_id',
                'permanent_union_id',
            ];

            foreach ($cols as $c) {
                if (Schema::hasColumn('students', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('class_test_result_details')) {
            return;
        }

        Schema::create('class_test_result_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('class_test_result_id')->index();
            $table->unsignedBigInteger('student_id')->index();
            $table->decimal('total_mark', 5)->nullable();
            $table->enum('result_status', ['PASSED', 'FAILED'])->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_test_result_details');
    }
};

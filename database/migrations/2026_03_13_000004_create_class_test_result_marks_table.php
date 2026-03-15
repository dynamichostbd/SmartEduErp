<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('class_test_result_marks')) {
            return;
        }

        Schema::create('class_test_result_marks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('class_test_result_details_id')->index();
            $table->unsignedBigInteger('subject_id')->index();
            $table->decimal('mark', 5)->nullable();
            $table->decimal('exam_mark', 5)->nullable();
            $table->decimal('pass_mark', 5)->nullable();
            $table->enum('result_status', ['PASSED', 'FAILED'])->nullable();
            $table->string('created_by', 100)->nullable();
            $table->string('created_ip', 50)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('updated_ip', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_test_result_marks');
    }
};

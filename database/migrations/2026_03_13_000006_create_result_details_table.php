<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('result_details')) {
            return;
        }

        Schema::create('result_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('result_id')->index();
            $table->unsignedBigInteger('student_id')->index();
            $table->decimal('total_mark_without_additional', 10)->nullable();
            $table->decimal('total_mark', 10)->nullable();
            $table->decimal('gpa_without_additional', 5)->nullable();
            $table->decimal('gpa', 5)->nullable();
            $table->string('letter_grade', 5)->nullable();
            $table->enum('result_status', ['PASSED', 'FAILED'])->nullable();
            $table->integer('merit_position_in_department')->nullable();
            $table->integer('merit_position_in_class')->nullable();

            $table->unique(['result_id', 'student_id'], 'result_details_result_id_student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_details');
    }
};

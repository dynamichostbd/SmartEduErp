<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('class_test_results')) {
            return;
        }

        Schema::create('class_test_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('academic_session_id')->index();
            $table->unsignedBigInteger('department_id')->index();
            $table->unsignedBigInteger('academic_qualification_id')->index();
            $table->unsignedBigInteger('academic_class_id')->index();
            $table->unsignedBigInteger('exam_id')->index();
            $table->date('exam_date')->nullable();
            $table->date('published_date')->nullable();
            $table->enum('status', ['draft', 'published', 'deactive'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_test_results');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('results')) {
            return;
        }

        Schema::create('results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('academic_session_id')->index();
            $table->unsignedBigInteger('department_id')->index();
            $table->unsignedBigInteger('academic_qualification_id')->index();
            $table->unsignedBigInteger('academic_class_id')->index();
            $table->unsignedBigInteger('exam_id')->index();
            $table->tinyInteger('total_exam_subjects')->default(7);
            $table->date('published_date')->nullable();
            $table->tinyInteger('child_subject_enabled')->nullable()->default(0);
            $table->longText('child_subject_enabled_subject_ids')->nullable();
            $table->enum('status', ['draft', 'published', 'deactive'])->default('draft');
            $table->string('created_by', 100)->nullable();
            $table->string('created_ip', 50)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('updated_ip', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};

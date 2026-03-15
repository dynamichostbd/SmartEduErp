<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('result_marks')) {
            return;
        }

        Schema::create('result_marks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('result_details_id')->index();
            $table->unsignedBigInteger('subject_id')->index();
            $table->decimal('ct_mark', 5)->nullable();
            $table->decimal('cq_mark', 5)->nullable();
            $table->decimal('mcq_mark', 5)->nullable();
            $table->decimal('practical_mark', 5)->nullable();
            $table->decimal('obtained_mark', 5)->nullable();
            $table->decimal('total_mark', 5)->nullable();
            $table->decimal('gpa', 5)->nullable();
            $table->string('letter_grade', 5)->nullable();
            $table->string('pass_marks', 255)->nullable();
            $table->tinyInteger('additional_subject')->default(0);
            $table->tinyInteger('is_absent')->default(0);
            $table->integer('sorting')->default(0);
            $table->string('created_by', 100)->nullable();
            $table->string('created_ip', 50)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('updated_ip', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_marks');
    }
};

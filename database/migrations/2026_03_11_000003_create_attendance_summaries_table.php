<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedBigInteger('academic_session_id')->index();
            $table->unsignedBigInteger('academic_qualification_id')->index();
            $table->unsignedBigInteger('department_id')->index();
            $table->unsignedBigInteger('academic_class_id')->index();
            $table->unsignedBigInteger('admit_card_id')->nullable()->index();
            $table->decimal('present_percent', 5, 2)->default(100);
            $table->unsignedInteger('total_class')->default(1);
            $table->string('created_by', 100)->nullable();
            $table->string('created_ip', 50)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('updated_ip', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_summaries');
    }
};

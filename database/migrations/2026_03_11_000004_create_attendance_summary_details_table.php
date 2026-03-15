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
        Schema::create('attendance_summary_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('attendance_summarie_id')->index();
            $table->string('student_id', 30)->index();
            $table->unsignedInteger('total_present')->default(0);
            $table->unsignedInteger('total_absent')->default(0);
            $table->decimal('present_percentage', 6, 2)->default(0);
            $table->enum('status', ['P', 'A'])->default('A');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_summary_details');
    }
};

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
        Schema::create('attendance_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('attendance_id')->index();
            $table->string('student_id', 30)->index();
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->string('type')->nullable();
            $table->string('device_identifier')->nullable();
            $table->string('rfid')->nullable();
            $table->enum('status', ['P', 'A'])->default('A');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_details');
    }
};

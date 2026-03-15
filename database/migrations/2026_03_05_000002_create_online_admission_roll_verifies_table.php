<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_admission_roll_verifies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('academic_session_id')->index();
            $table->unsignedBigInteger('academic_qualification_id')->index();
            $table->unsignedBigInteger('department_id')->index();
            $table->unsignedBigInteger('academic_class_id')->index();
            $table->longText('roll_lists');
            $table->longText('name_lists')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_admission_roll_verifies');
    }
};

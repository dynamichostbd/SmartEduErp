<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('grade_management')) {
            return;
        }

        Schema::create('grade_management', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('from_mark', 10);
            $table->decimal('to_mark', 10);
            $table->string('grade', 5);
            $table->decimal('gpa', 10);
            $table->decimal('from_gpa', 10)->nullable();
            $table->decimal('to_gpa', 10)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_management');
    }
};

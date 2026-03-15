<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academic_sessions')) {
            return;
        }

        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->tinyInteger('current')->default(0);
            $table->string('description')->nullable();
            $table->tinyInteger('online_admission')->default(0);
            $table->tinyInteger('registration')->default(0);
            $table->tinyInteger('application_fee')->default(0);
            $table->enum('status', ['draft', 'active', 'deactive'])->default('active');
            $table->string('created_by', 100)->nullable();
            $table->string('created_ip', 50)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('updated_ip', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_sessions');
    }
};

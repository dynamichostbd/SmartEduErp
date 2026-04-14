<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hostel_fee_generates')) {
            return;
        }

        Schema::create('hostel_fee_generates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('hostel_id');
            $table->unsignedBigInteger('financial_year_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_fee_generates');
    }
};

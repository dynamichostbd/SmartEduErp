<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hostel_fee_generate_details')) {
            return;
        }

        Schema::create('hostel_fee_generate_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('hostel_fee_generate_id')->index('hostel_fee_generate_details_hostel_fee_generate_id_foreign');
            $table->unsignedBigInteger('account_head_id')->nullable();
            $table->date('date')->nullable();
            $table->decimal('amount', 10)->nullable();
            $table->tinyInteger('is_establishment')->default(0);
            $table->tinyInteger('sorting')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->string('created_by', 100)->nullable();
            $table->string('created_ip', 50)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('updated_ip', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_fee_generate_details');
    }
};

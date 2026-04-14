<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hostel_fee_setups')) {
            return;
        }

        Schema::create('hostel_fee_setups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('hostel_id')->index('hostel_fee_setups_hostel_id_foreign');
            $table->unsignedBigInteger('account_head_id')->index('hostel_fee_setups_account_head_id_foreign');
            $table->integer('amount');
            $table->string('store_id')->nullable();
            $table->string('store_password')->nullable();
            $table->string('status', 20)->default('active');
            $table->string('created_by', 100)->nullable();
            $table->string('created_ip', 50)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('updated_ip', 50)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_fee_setups');
    }
};

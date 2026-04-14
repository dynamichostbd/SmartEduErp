<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hostel_payment_details')) {
            return;
        }

        Schema::create('hostel_payment_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('hostel_payment_id')->index('hostel_payment_details_hostel_payment_id_foreign');
            $table->unsignedBigInteger('hostel_fee_generate_details_id')->index('hostel_payment_details_hostel_fee_generate_details_id_foreign');
            $table->unsignedBigInteger('financial_year_id');
            $table->unsignedBigInteger('account_head_id')->index('hostel_payment_details_account_head_id_foreign');
            $table->date('invoice_date')->nullable();
            $table->decimal('amount', 10);
            $table->string('status', 15)->default('pending')->comment('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_payment_details');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hostel_payments')) {
            return;
        }

        Schema::create('hostel_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->index('hostel_payments_student_id_foreign');
            $table->unsignedBigInteger('hostel_id')->index('hostel_payments_hostel_id_foreign');
            $table->integer('financial_year_id');
            $table->unsignedBigInteger('academic_session_id')->index('hostel_payments_academic_session_id_foreign');
            $table->unsignedBigInteger('department_id')->index('hostel_payments_department_id_foreign');
            $table->unsignedBigInteger('academic_qualification_id')->index('hostel_payments_academic_qualification_id_foreign');
            $table->unsignedBigInteger('academic_class_id')->index('hostel_payments_academic_class_id_foreign');
            $table->string('admission_id', 40)->nullable();
            $table->integer('college_roll')->nullable();
            $table->string('reg_no', 50)->nullable();
            $table->date('invoice_date');
            $table->string('invoice_number', 50)->unique();
            $table->decimal('amount', 10);
            $table->integer('discount_percent')->nullable();
            $table->decimal('discount_amount', 10)->nullable();
            $table->decimal('service_charge', 10)->nullable();
            $table->decimal('paid_amount', 10)->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 10)->default('SSL');
            $table->string('card_type', 100)->nullable();
            $table->string('bank_trans_id')->nullable();
            $table->string('status', 15)->default('pending')->comment('pending');
            $table->enum('created_from', ['web', 'app'])->default('web');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_payments');
    }
};
